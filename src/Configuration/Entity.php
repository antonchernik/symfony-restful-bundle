<?php

declare(strict_types=1);

namespace RestfulBundle\Configuration;

use RestfulBundle\Dictionary\Messages;

/**
 * @Annotation
 */
class Entity extends MapperParamConverter
{
    private string $notFoundMessage = Messages::VALIDATION__ENTITY__NOT_FOUND;

    public function setNotFoundMessage(string $message): self
    {
        $this->notFoundMessage = $message;

        return $this;
    }

    public function getNotFoundMessage(): string
    {
        return $this->notFoundMessage;
    }
}
