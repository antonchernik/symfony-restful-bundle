<?php

declare(strict_types=1);

namespace RestfulBundle;

use RestfulBundle\DependencyInjection\Compiler\ExceptionHandlerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RestfulBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ExceptionHandlerPass());
    }
}
