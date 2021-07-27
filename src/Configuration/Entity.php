<?php

declare(strict_types=1);

namespace RestfulBundle\Configuration;

use RestfulBundle\Dictionary\Messages;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity as DoctrineEntity;

/**
 * @Annotation
 */
class Entity extends DoctrineEntity implements ConfigurationInterface
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
