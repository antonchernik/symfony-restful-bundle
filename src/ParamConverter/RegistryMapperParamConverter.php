<?php

declare(strict_types=1);

namespace RestfulBundle\ParamConverter;

use RestfulBundle\Configuration\RegistryMapper;
use RestfulBundle\Request\RequestMapperInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;

class RegistryMapperParamConverter extends MapperConverter
{
    use ContainerAwareTrait;

    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $registry = $this->getRegistry($configuration->getRegistry());

        $registryMethod = $configuration->getMethod();
        if (!is_callable([$registry, $registryMethod])) {
            throw new \BadMethodCallException($registryMethod . ' method does not exist.');
        }

        $requestDTO = $registry->$registryMethod($request);
        $configuration->setClass($requestDTO);

        return parent::apply($request, $configuration);
    }

    public function supports(ParamConverter $configuration): bool
    {
        return $configuration instanceof RegistryMapper;
    }

    protected function getRegistry(string $class): RequestMapperInterface
    {
        return $this->container->has($class)
            ? $this->container->get($class)
            : new $class();
    }
}

