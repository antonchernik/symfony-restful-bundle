<?php

declare(strict_types=1);

namespace RestfulBundle\Service;

class RequestTracker
{
    public function __construct(
        protected string $requestId
    ) {}

    public function getRequestId(): string
    {
        return $this->requestId;
    }
}
