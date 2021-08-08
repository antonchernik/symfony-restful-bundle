<?php

namespace RestfulBundle\Handler;

use Throwable;

interface ExceptionHandlerInterface
{
    public function supports(): string;

    public function getBody(Throwable $throwable): array;

    /**
     * Extracts status code from the exception
     */
    public function getStatusCode(Throwable $throwable): int;

    /**
     * Extracts headers from the exception
     */
    public function getHeaders(Throwable $throwable): array;
}