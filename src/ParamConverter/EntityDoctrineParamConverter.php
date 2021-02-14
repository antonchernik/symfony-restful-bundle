<?php

declare(strict_types=1);

namespace RestfulBundle\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use RestfulBundle\Configuration\Entity;

class EntityDoctrineParamConverter extends DoctrineParamConverter implements ParamConverterInterface
{
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        try {
            parent::apply($request, $configuration);
        } catch (NotFoundHttpException $exception) {
            throw new NotFoundHttpException($configuration->getNotFoundMessage());
        }

        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        return $configuration instanceof Entity && parent::supports($configuration);
    }
}
