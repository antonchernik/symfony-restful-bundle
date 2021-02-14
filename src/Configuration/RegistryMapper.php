<?php

declare(strict_types=1);

namespace RestfulBundle\Configuration;

/**
 * @Annotation
 */
class RegistryMapper extends MapperParamConverter
{
    private string $registry;

    private string $method = 'getRequestDTO';

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getRegistry(): string
    {
        return $this->registry;
    }

    public function setRegistry(string $registry): self
    {
        $this->registry = $registry;

        return $this;
    }
}
