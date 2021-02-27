<?php

declare(strict_types=1);

namespace RestfulBundle\Exception\Handler;

use AutoMapperPlus\Exception\UnsupportedSourceTypeException;
use RestfulBundle\Dictionary\Messages;
use Symfony\Component\HttpFoundation\Response;

class MapperExceptionHandler extends ExceptionHandler
{
    public function __construct(bool $debug = false)
    {
        parent::__construct($debug);
    }

    public function supports(): string
    {
        return UnsupportedSourceTypeException::class;
    }

    public function getBody(\Throwable $throwable): array
    {
        $data['code'] = Response::HTTP_BAD_REQUEST;
        $data['message'] = Messages::VALIDATION_ERROR;
        $data['errors'] = [(string)$throwable->getMessage()];

        return $data;
    }

    public function getStatusCode(\Throwable $throwable): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
