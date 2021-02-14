<?php

declare(strict_types=1);

namespace RestfulBundle\Configuration;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Annotation
 */
class MapperParamConverter extends ParamConverter
{
    /**
     * @var array
     */
    private $validationGroups = array('Default');

    /**
     * @return array
     */
    public function getValidationGroups(): array
    {
        return $this->validationGroups;
    }

    /**
     * @param array $validationGroups
     *
     * @return $this
     */
    public function setValidationGroups(array $validationGroups)
    {
        $this->validationGroups = $validationGroups;

        return $this;
    }
}
