<?php

namespace Zhortein\ElasticEntityBundle\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Zhortein\ElasticEntityBundle\Event\ElasticEntityEvent;

class ElasticEntityListener
{
    #[AsEventListener(event: 'elastic_entity.pre_persist')]
    public function onPrePersist(ElasticEntityEvent $event): void
    {
        // Logic before persisting an entity
    }

    #[AsEventListener(event: 'elastic_entity.post_persist')]
    public function onPostPersist(ElasticEntityEvent $event): void
    {
        // Logic after persisting an entity
    }

    #[AsEventListener(event: 'elastic_entity.pre_update')]
    public function onPreUpdate(ElasticEntityEvent $event): void
    {
        // Logic before updating an entity
    }

    #[AsEventListener(event: 'elastic_entity.post_update')]
    public function onPostUpdate(ElasticEntityEvent $event): void
    {
        // Logic after updating an entity
    }

    #[AsEventListener(event: 'elastic_entity.pre_remove')]
    public function onPreRemove(ElasticEntityEvent $event): void
    {
        // Logic before removing an entity
    }

    #[AsEventListener(event: 'elastic_entity.post_remove')]
    public function onPostRemove(ElasticEntityEvent $event): void
    {
        // Logic after removing an entity
    }
}
