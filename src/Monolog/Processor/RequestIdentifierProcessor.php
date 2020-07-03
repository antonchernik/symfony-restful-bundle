<?php

declare(strict_types=1);

namespace RestfulBundle\Monolog\Processor;

use Monolog\Processor\ProcessorInterface;

class RequestIdentifierProcessor implements ProcessorInterface
{
    /**
     * @var string
     */
    private string $requestId;

    public function __invoke(array $records)
    {
        $records['extra']['requestId'] = $this->requestId;
        return $records;
    }

    /**
     * @param string $requestId
     */
    public function __construct(string $requestId)
    {
        $this->requestId = $requestId;
    }
}
