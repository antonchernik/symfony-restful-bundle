<?php

declare(strict_types=1);

namespace RestfulBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionListener
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var string
     */
    private string $requestId;

    /**
     * ExceptionListener constructor.
     * @param LoggerInterface $logger
     * @param string          $requestId
     */
    public function __construct(LoggerInterface $logger, string $requestId)
    {
        $this->logger = $logger;
        $this->requestId = $requestId;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $this->logger->critical($exception->getMessage().' File '.$exception->getFile().' Line '.$exception->getLine().' Trace: '.$exception->getTraceAsString());
        if ($exception instanceof HttpException) {
            $event->setResponse(new JsonResponse(['code' => $exception->getStatusCode(), 'requestId' => $this->requestId, 'message' => $exception->getMessage(), 'data' => []]));
        }
    }
}
