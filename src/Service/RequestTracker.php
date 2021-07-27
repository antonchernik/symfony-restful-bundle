<?php

declare(strict_types=1);

namespace RestfulBundle\Service;

class RequestTracker
{
    protected string $requestId;

    public function __construct(string $requestId)
    {
        $this->requestId = $requestId;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }
}
