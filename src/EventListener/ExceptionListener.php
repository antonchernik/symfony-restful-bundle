<?php

declare(strict_types=1);

namespace RestfulBundle\EventListener;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RestfulBundle\ExceptionHandlerResolver;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const LOG_TEMPLATE = '%s File %s Line %s Trace: %s';

    public function __construct(
        protected string $requestId,
        protected ExceptionHandlerResolver $handlerResolver
    ) {}

    public function onKernelException(ExceptionEvent $event): void
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
            self::LOG_TEMPLATE,
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
