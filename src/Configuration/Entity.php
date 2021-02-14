<?php

declare(strict_types=1);

namespace RestfulBundle\Configuration;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity as DoctrineEntity;

/**
 * @Annotation
 */
class Entity extends DoctrineEntity implements ConfigurationInterface
{
    private string $notFoundMessage = 'entity.not_found';

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
