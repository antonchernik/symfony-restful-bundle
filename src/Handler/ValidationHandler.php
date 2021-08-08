<?php

declare(strict_types=1);

namespace RestfulBundle\Handler;

use RestfulBundle\Exception\ValidationExceptionInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Throwable;

class ValidationHandler extends ExceptionHandler
{
    public function __construct(
        bool $debug = false,
        protected array $messagesMap = [],
        protected bool $snakeCase = true
    ) {
        parent::__construct($debug);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(): string
    {
        return ValidationExceptionInterface::class;
    }

    public function getBody(Throwable $throwable): array
    {
        $data = parent::getBody($throwable);

        /** @var ValidationExceptionInterface $throwable */
        $data['errors'] = $this->collectErrorsToArray($throwable->getErrors());

        return $data;
    }

    protected function collectErrorsToArray(array $errors, string $prefix = ''): array
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

    protected function combinePrefixWithField(string $prefix, string $field, string $combinePattern): string
    {
        return '' === $prefix ? $field : sprintf($combinePattern, $prefix, $field);
    }

    protected function decorateField(string $prefix, string $field, string $combinePattern): string
    {
        $field = $this->combinePrefixWithField($prefix, $field, $combinePattern);
        if ($this->snakeCase) {
            return strtolower(preg_replace('/[A-Z]/', '_\\0', $field));
        }

        return lcfirst(str_replace('_', '', ucwords($field, '_')));
    }
}
