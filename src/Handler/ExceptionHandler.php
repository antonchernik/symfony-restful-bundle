<?php

declare(strict_types=1);

namespace RestfulBundle\Handler;

use RestfulBundle\Dictionary\Messages;
use RestfulBundle\Exception\HttpAwareExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionHandler implements ExceptionHandlerInterface
{
    protected bool $debug = false;

    public function __construct(bool $debug = false)
    {
        $this->debug = $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(): string
    {
        return \Throwable::class;
    }

    public function getBody(\Throwable $throwable): array
    {
        $statusCode = $this->getStatusCode($throwable);

        $body = [
            'code'    => $statusCode,
            'message' => $this->getExceptionMessage($throwable, $statusCode),
        ];

        if (true === $this->debug) {
            $body['exception'] = [
                'message' => $throwable->getMessage(),
                'class'   => get_class($throwable),
                'file'    => $throwable->getFile(),
                'line'    => $throwable->getLine(),
                'trace'   => $throwable->getTrace(),
            ];
        }

        return $body;
    }

    /**
     * Extracts status code from the exception
     */
    public function getStatusCode(\Throwable $throwable): int
    {
        return ($throwable instanceof HttpExceptionInterface) || ($throwable instanceof HttpAwareExceptionInterface)
            ? $throwable->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    /**
     * Extracts headers from the exception
     */
    public function getHeaders(\Throwable $throwable): array
    {
        return ($throwable instanceof HttpExceptionInterface) || ($throwable instanceof HttpAwareExceptionInterface)
            ? $throwable->getHeaders()
            : [];
    }

    /**
     * Extracts the exception message.
     */
    protected function getExceptionMessage(\Throwable $throwable, int $statusCode = null): string
    {
        $message = (string) $throwable->getMessage();

        if ('' !== $message) {

            return $message;
        }

        switch (true) {
            case $statusCode == Response::HTTP_FORBIDDEN:
                $message = Messages::ACCESS_DENIED;
                break;
            case preg_match('/^5\d\d$/', $statusCode):
                $message = Messages::INTERNAL_SERVER_ERROR;
                break;
        }

        return $message;
    }
}
