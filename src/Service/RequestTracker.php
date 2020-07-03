<?php

declare(strict_types=1);

namespace RestfulBundle\Service;

class RequestTracker
{
    /**
     * @var string
     */
    private string $requestId;

    /**
     * RequestTracker constructor.
     * @param string $requestId
     */
    public function __construct(string $requestId)
    {
        $this->requestId = $requestId;
    }

    /**
     * @return string
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }
}
