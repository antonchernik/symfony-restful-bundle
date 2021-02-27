<?php

declare(strict_types=1);

namespace RestfulBundle\Exception\Handler;

use JetBrains\PhpStorm\Pure;
use RestfulBundle\Exception\ValidationExceptionInterface;
use Symfony\Component\Validator\ConstraintViolation;

class ValidationHandler extends ExceptionHandler
{
    private array $messagesMap = [];

    private bool $snakeCase;

    public function __construct(bool $debug = false, array $messagesMap = [], bool $snakeCase = true)
    {
        parent::__construct($debug);
        $this->messagesMap = $messagesMap;
        $this->snakeCase = $snakeCase;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(): string
    {
        return ValidationExceptionInterface::class;
    }

    public function getBody(\Throwable $throwable): array
    {
        $data = parent::getBody($throwable);

        /** @var ValidationExceptionInterface $throwable */
        $data['errors'] = $this->collectErrorsToArray($throwable->getErrors());

        return $data;
    }

    private function collectErrorsToArray(array $errors, string $prefix = ''): array
    {
        $data = [];
        foreach ($errors as $key => $error) {
            $key = (string) $key;
            switch (true) {
                case $error instanceof ConstraintViolation:
                    $field = $this->decorateField($prefix, $error->getPropertyPath(), '%s.%s');

                    $message = $this->messagesMap[$error->getCode()] ?? $error->getMessage();
                    if (!isset($data[$field]) || !in_array($message, $data[$field])) {
                        $data[$field][] = $message;
                    }
                    break;
                case is_array($error):
                    $pr = $this->combinePrefixWithField($prefix, $key, '%s[%s]');
                    $data = array_merge($data, $this->collectErrorsToArray($error, $pr));
                    break;
                default:
                    $field = $this->decorateField($prefix, $key, '%s[%s]');
                    $data[$field] = $error;
            }
        }

        return $data;
    }

    private function combinePrefixWithField(string $prefix, string $field, string $combinePattern): string
    {
        return '' === $prefix ? $field : sprintf($combinePattern, $prefix, $field);
    }

    private function decorateField(string $prefix, string $field, string $combinePattern): string
    {
        $field = $this->combinePrefixWithField($prefix, $field, $combinePattern);
        if ($this->snakeCase) {
            return strtolower(preg_replace('/[A-Z]/', '_\\0', $field));
        }

        return lcfirst(str_replace('_', '', ucwords($field, '_')));
    }
}
