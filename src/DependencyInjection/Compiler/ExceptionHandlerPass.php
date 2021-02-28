<?php

declare(strict_types=1);

namespace RestfulBundle\DependencyInjection\Compiler;

use RestfulBundle\ExceptionHandlerResolver;
use RestfulBundle\Handler\MapperExceptionHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Finds all tagged services with tag "exception_handler" and registers it as exception-handler
 */
class ExceptionHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $resolver = $container->getDefinition(ExceptionHandlerResolver::class);

        $this->registerCustomHandlers($container);

        $handlers = [];

        foreach ($container->findTaggedServiceIds('exception_handler') as $id => $tags) {
            $handlers[$id] = $tags[0]['priority'] ?? 0;
        }

        uasort(
            $handlers,
            function ($priority1, $priority2) {
                if ($priority1 === $priority2) {
                    return 0;
                }

                return ($priority1 < $priority2) ? 1 : -1;
            }
        );

        foreach (array_keys($handlers) as $handlerId) {
            $resolver->addMethodCall('addExceptionHandler', [$container->getDefinition($handlerId)]);
        }
    }

    private function registerCustomHandlers(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        if (array_key_exists('DTOBundle', $bundles)) {
            $container->register(MapperExceptionHandler::class, MapperExceptionHandler::class)
                ->setArgument('$debug', $container->getParameter('kernel.debug'))
                ->addTag('exception_handler', ['priority' => 100]);
        }
    }
}
