<?php

declare(strict_types=1);

namespace RestfulBundle\Exception;

use RestfulBundle\Exception\Handler\ExceptionHandlerInterface;

class ExceptionHandlerResolver
{
    /**
     * @var ExceptionHandlerInterface[]
     */
    private array $handlers;

    /**
     * @param ExceptionHandlerInterface $handler
     */
    public function addExceptionHandler(ExceptionHandlerInterface $handler)
    {
        $this->handlers[] = $handler;
    }

    /**
     * @param \Throwable $e
     *
     * @return ExceptionHandlerInterface
     */
    public function resolve(\Throwable $e)
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
