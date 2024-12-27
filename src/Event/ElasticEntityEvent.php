<?php

namespace Zhortein\ElasticEntityBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Zhortein\ElasticEntityBundle\Contracts\ElasticEntityInterface;

class ElasticEntityEvent extends Event
{
    public function __construct(
        private readonly ElasticEntityInterface $entity,
    ) {
    }

    public function getEntity(): ElasticEntityInterface
    {
        return $this->entity;
    }
}
