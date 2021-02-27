<?php

declare(strict_types=1);

namespace RestfulBundle\EventListener;

use Psr\Log\LoggerInterface;
use RestfulBundle\Exception\ExceptionHandlerResolver;
use RestfulBundle\Exception\ValidationExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionListener
{
    private LoggerInterface $logger;
    private string $requestId;
    private ExceptionHandlerResolver $handlerResolver;

    /**
     * @param LoggerInterface $logger
     * @param string          $requestId
     */
    public function __construct(LoggerInterface $logger, string $requestId, ExceptionHandlerResolver $handlerResolver)
    {
        $this->logger = $logger;
        $this->requestId = $requestId;
        $this->handlerResolver = $handlerResolver;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof \Exception) {
            return;
        }

        $handler = $this->handlerResolver->resolve($exception);
        $content = $handler->getBody($exception) + ['requestId' => $this->requestId];
        $statusCode = $handler->getStatusCode($exception);
        $headers = $handler->getHeaders($exception);
        $event->setResponse(new JsonResponse($content, $statusCode, $headers));

        $message = sprintf(
            '%s File %s Line %s Trace: %s',
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString(),
        );

        if (preg_match('/^5\d\d$/', (string) $statusCode)) {
            $this->logger->critical($message);
        } else {
            $this->logger->error($message);
        }
    }
}
