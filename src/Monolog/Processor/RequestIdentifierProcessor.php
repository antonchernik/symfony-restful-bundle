<?php

declare(strict_types=1);

namespace RestfulBundle\Monolog\Processor;

use Monolog\Processor\ProcessorInterface;

class RequestIdentifierProcessor implements ProcessorInterface
{
    public function __construct(string $requestId)
    {
        $this->requestId = $requestId;
    }

    private string $requestId;

    public function __invoke(array $record): array
    {
        $record['extra']['requestId'] = $this->requestId;
        return $record;
    }
}
