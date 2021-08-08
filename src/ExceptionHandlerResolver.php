<?php

declare(strict_types=1);

namespace RestfulBundle;

use RestfulBundle\Handler\ExceptionHandlerInterface;
use Throwable;

class ExceptionHandlerResolver
{
    protected array $handlers;

    public function addExceptionHandler(ExceptionHandlerInterface $handler)
    {
        $this->handlers[] = $handler;
    }

    public function resolve(Throwable $e): ?ExceptionHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            $supports = $handler->supports();
            if ($e instanceof $supports) {
                return $handler;
            }
        }

        return null;
    }
}
