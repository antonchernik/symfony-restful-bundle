<?php

namespace RestfulBundle\Exception;

interface ValidationExceptionInterface
{
    public function __construct(array $errors, string $message);
    public function getErrors(): array;
}