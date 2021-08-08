<?php

declare(strict_types=1);

namespace RestfulBundle\ParamConverter;

use DTOBundle\Mapper\AutoMapperAwareInterface;
use RestfulBundle\Configuration\MapperParamConverter;
use RestfulBundle\Exception\ValidationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MapperConverter implements ParamConverterInterface
{
    public function __construct(
        protected AutoMapperAwareInterface $mapper,
        protected ValidatorInterface $validator
    ) {}

    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $class = $configuration->getClass();
        $dto = $this
            ->mapper
            ->convert(
                array_merge(
                    $request->attributes->get('_route_params', []),
                    $request->query->all(),
                    $request->request->all()
                ),
                $class,
                []
            );

        $errors = $this->validator->validate($dto, null, $configuration->getValidationGroups());
        if (count($errors) > 0) {
            throw new ValidationException((array) $errors->getIterator());
        }

        $request->attributes->set($configuration->getName(), $dto);

        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        return $configuration instanceof MapperParamConverter;
    }
}
