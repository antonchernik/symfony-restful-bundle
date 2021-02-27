<?php

namespace RestfulBundle\Exception;

interface HttpAwareExceptionInterface
{
    public function getStatusCode(): int;
    public function getHeaders(): array;
}