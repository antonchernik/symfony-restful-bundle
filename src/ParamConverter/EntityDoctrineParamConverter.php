<?php

declare(strict_types=1);

namespace RestfulBundle\ParamConverter;

use Doctrine\DBAL\Types\ConversionException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use DTOBundle\Mapper\AutoMapperAwareInterface;
use Exception;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;
use RestfulBundle\Configuration\Entity;
use RestfulBundle\Exception\ValidationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntityDoctrineParamConverter extends MapperConverter
{
    private array $defaultOptions;

    public function __construct(
        protected ?ManagerRegistry $registry = null,
        protected ?ExpressionLanguage $expressionLanguage = null,
        AutoMapperAwareInterface $mapper,
        ValidatorInterface $validator,
        array $options = []
    ) {
        $defaultValues = [
            'entity_manager' => null,
            'exclude' => [],
            'mapping' => [],
            'strip_null' => false,
            'expr' => null,
            'id' => null,
            'evict_cache' => false,
        ];

        $this->defaultOptions = array_merge($defaultValues, $options);
        parent::__construct($mapper, $validator);
    }

    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $this->validateDTO($request, $configuration);

        try {
            $name = $configuration->getName();
            $class = $configuration->getClass();
            $options = $this->getOptions($configuration);

            if (null === $request->attributes->get($name, false)) {
                $configuration->setIsOptional(true);
            }

            $errorMessage = null;
            if ($expr = $options['expr']) {
                $object = $this->findViaExpression($class, $request, $expr, $options, $configuration);

                if (null === $object) {
                    $errorMessage = sprintf('The expression "%s" returned null', $expr);
                }

                // find by identifier?
            } elseif (false === $object = $this->find($class, $request, $options, $name)) {
                // find by criteria
                if (false === $object = $this->findOneBy($class, $request, $options)) {
                    if ($configuration->isOptional()) {
                        $object = null;
                    } else {
                        throw new \LogicException(sprintf('Unable to guess how to get a Doctrine instance from the request information for parameter "%s".', $name));
                    }
                }
            }

            if (null === $object && false === $configuration->isOptional()) {
                $message = sprintf('%s object not found by the @%s annotation.', $class, $this->getAnnotationName($configuration));
                if ($errorMessage) {
                    $message .= ' '.$errorMessage;
                }
                throw new NotFoundHttpException($message);
            }

            $request->attributes->set($name, $object);
        } catch (NotFoundHttpException $exception) {
            throw new NotFoundHttpException($configuration->getNotFoundMessage());
        }

        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        // if there is no manager, this means that only Doctrine DBAL is configured
        if (null === $this->registry || !\count($this->registry->getManagerNames())) {
            return false;
        }

        if (null === $configuration->getClass()) {
            return false;
        }

        $options = $this->getOptions($configuration, false);

        $em = $this->getManager($options['entity_manager'], $configuration->getClass());
        if (null === $em) {
            return false;
        }

        return $configuration instanceof Entity && !$em->getMetadataFactory()->isTransient($configuration->getClass());
    }

    private function find($class, Request $request, $options, $name): mixed
    {
        if ($options['mapping'] || $options['exclude']) {
            return false;
        }

        $id = $this->getIdentifier($request, $options, $name);

        if (false === $id || null === $id) {
            return false;
        }

        $om = $this->getManager($options['entity_manager'], $class);
        if ($options['evict_cache'] && $om instanceof EntityManagerInterface) {
            $cacheProvider = $om->getCache();
            if ($cacheProvider && $cacheProvider->containsEntity($class, $id)) {
                $cacheProvider->evictEntity($class, $id);
            }
        }

        try {
            return $om->getRepository($class)->find($id);
        } catch (NoResultException $e) {
            return false;
        } catch (ConversionException $e) {
            return false;
        }
    }

    private function getIdentifier(Request $request, $options, $name): mixed
    {
        if (null !== $options['id']) {
            if (!\is_array($options['id'])) {
                $name = $options['id'];
            } elseif (\is_array($options['id'])) {
                $id = [];
                foreach ($options['id'] as $field) {
                    if (false !== strstr($field, '%s')) {
                        // Convert "%s_uuid" to "foobar_uuid"
                        $field = sprintf($field, $name);
                    }
                    $id[$field] = $request->attributes->get($field);
                }

                return $id;
            }
        }

        if ($request->attributes->has($name)) {
            return $request->attributes->get($name);
        }

        if ($request->attributes->has('id') && !$options['id']) {
            return $request->attributes->get('id');
        }

        return false;
    }

    private function findOneBy($class, Request $request, $options)
    {
        if (!$options['mapping']) {
            $keys = $request->attributes->keys();
            $options['mapping'] = $keys ? array_combine($keys, $keys) : [];
        }

        foreach ($options['exclude'] as $exclude) {
            unset($options['mapping'][$exclude]);
        }

        if (!$options['mapping']) {
            return false;
        }

        // if a specific id has been defined in the options and there is no corresponding attribute
        // return false in order to avoid a fallback to the id which might be of another object
        if ($options['id'] && null === $request->attributes->get($options['id'])) {
            return false;
        }

        $criteria = [];
        $em = $this->getManager($options['entity_manager'], $class);
        $metadata = $em->getClassMetadata($class);

        foreach ($options['mapping'] as $attribute => $field) {
            if (
                $metadata->hasField($field) ||
                ($metadata->hasAssociation($field) && $metadata->isSingleValuedAssociation($field))
            ) {
                $criteria[$field] = $request->attributes->get($attribute);
            }
        }

        if ($options['strip_null']) {
            $criteria = array_filter($criteria, function ($value) {
                return null !== $value;
            });
        }

        if (!$criteria) {
            return false;
        }

        try {
            return $em->getRepository($class)->findOneBy($criteria);
        } catch (NoResultException $e) {
            return;
        } catch (ConversionException $e) {
            return;
        }
    }

    private function findViaExpression($class, Request $request, $expression, $options, ParamConverter $configuration)
    {
        if (null === $this->expressionLanguage) {
            throw new LogicException(sprintf('To use the @%s tag with the "expr" option, you need to install the ExpressionLanguage component.', $this->getAnnotationName($configuration)));
        }

        $repository = $this->getManager($options['entity_manager'], $class)->getRepository($class);
        $variables = array_merge($request->attributes->all(), ['repository' => $repository]);

        try {
            return $this->expressionLanguage->evaluate($expression, $variables);
        } catch (NoResultException $e) {
            return;
        } catch (ConversionException $e) {
            return;
        } catch (SyntaxError $e) {
            throw new LogicException(sprintf('Error parsing expression -- "%s" -- (%s).', $expression, $e->getMessage()), 0, $e);
        }
    }

    private function getOptions(ParamConverter $configuration, $strict = true): array
    {
        $passedOptions = $configuration->getOptions();

        $extraKeys = array_diff(array_keys($passedOptions), array_keys($this->defaultOptions));
        if ($extraKeys && $strict) {
            throw new InvalidArgumentException(sprintf('Invalid option(s) passed to @%s: "%s".', $this->getAnnotationName($configuration), implode(', ', $extraKeys)));
        }

        return array_replace($this->defaultOptions, $passedOptions);
    }

    private function getManager($name, $class): ?ObjectManager
    {
        if (null === $name) {
            return $this->registry->getManagerForClass($class);
        }

        return $this->registry->getManager($name);
    }

    private function getAnnotationName(ParamConverter $configuration): string
    {
        return (new ReflectionClass($configuration))->getShortName();
    }
}
