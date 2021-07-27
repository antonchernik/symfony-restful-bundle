<?php

declare(strict_types=1);

namespace RestfulBundle\Configuration;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Annotation
 */
class MapperParamConverter extends ParamConverter implements ConfigurationInterface
{
    protected array $validationGroups = ['Default'];

    public function getValidationGroups(): array
    {
        return $this->validationGroups;
    }

    public function setValidationGroups(array $validationGroups): self
    {
        $this->validationGroups = $validationGroups;

        return $this;
    }
}
