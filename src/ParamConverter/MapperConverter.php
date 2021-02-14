<?php

declare(strict_types=1);

namespace RestfulBundle\ParamConverter;

use DTOBundle\Mapper\MapperInterface;
use RestfulBundle\Configuration\MapperParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MapperConverter implements ParamConverterInterface
{
    private MapperInterface $mapper;

    private ValidatorInterface $validator;

    public function __construct(MapperInterface $mapper, ValidatorInterface $validator)
    {
        $this->validator = $validator;
        $this->mapper = $mapper;
    }

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
                $class
            );

        $this->validator->validate($dto, null, $configuration->getValidationGroups());

        $request->attributes->set($configuration->getName(), $dto);

        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        return $configuration instanceof MapperParamConverter;
    }
}
