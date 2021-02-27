<?php

namespace RestfulBundle\Handler;

interface ExceptionHandlerInterface
{
    public function supports(): string;

    public function getBody(\Throwable $throwable);

    /**
     * Extracts status code from the exception
     */
    public function getStatusCode(\Throwable $throwable): int;

    /**
     * Extracts headers from the exception
     */
    public function getHeaders(\Throwable $throwable): array;
}