<?php

declare(strict_types=1);

namespace RestfulBundle\Exception;

use RestfulBundle\Dictionary\Messages;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ValidationException extends HttpException implements ValidationExceptionInterface
{
    private array $errors;

    public function __construct(array $errors, string $message = Messages::VALIDATION__COMMON__ERROR)
    {
        $this->errors = $errors;
        parent::__construct(Response::HTTP_BAD_REQUEST, $message);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
