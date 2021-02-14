<?php

declare(strict_types=1);

namespace RestfulBundle\Configuration;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity as DoctrineEntity;

/**
 * @Annotation
 */
class Entity extends DoctrineEntity
{
    /**
     * @var string
     */
    private $notFoundMessage = 'entity.not_found';

    /**
     * @param string $message
     *
     * @return Entity
     */
    public function setNotFoundMessage(string $message): self
    {
        $this->notFoundMessage = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getNotFoundMessage(): string
    {
        return $this->notFoundMessage;
    }
}
