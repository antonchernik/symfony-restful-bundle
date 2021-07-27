<?php

declare(strict_types=1);

namespace RestfulBundle\Handler;

use AutoMapperPlus\Exception\UnsupportedSourceTypeException;
use JetBrains\PhpStorm\Pure;
use RestfulBundle\Dictionary\Messages;
use Symfony\Component\HttpFoundation\Response;

class MapperExceptionHandler extends ExceptionHandler
{
    public function supports(): string
    {
        return UnsupportedSourceTypeException::class;
    }

    public function getBody(\Throwable $throwable): array
    {
        $data['code'] = Response::HTTP_BAD_REQUEST;
        $data['message'] = Messages::VALIDATION__COMMON__ERROR;
        $data['errors'] = [$throwable->getMessage()];

        return $data;
    }

    public function getStatusCode(\Throwable $throwable): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
