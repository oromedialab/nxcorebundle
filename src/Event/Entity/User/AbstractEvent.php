<?php

namespace OroMediaLab\NxCoreBundle\Event\Entity\User;

use Symfony\Contracts\EventDispatcher\Event;
use OroMediaLab\NxCoreBundle\Entity\User;

abstract class AbstractEvent extends Event
{
    protected $entity;

    public function __construct(User $entity)
    {
        $this->entity = $entity;
    }

    public function getEntity()
    {
        return $this->entity;
    }
}
