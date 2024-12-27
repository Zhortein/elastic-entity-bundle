<?php

namespace Zhortein\ElasticEntityBundle\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Zhortein\ElasticEntityBundle\Event\ElasticEntityEvent;

class ElasticEntityListener
{
    #[AsEventListener(event: 'elastic_entity.pre_persist', method: 'onPrePersist')]
    public function onPrePersist(ElasticEntityEvent $event): void
    {
        // Logic before persisting an entity
    }

    #[AsEventListener(event: 'elastic_entity.post_persist', method: 'onPostPersist')]
    public function onPostPersist(ElasticEntityEvent $event): void
    {
        // Logic after persisting an entity
    }

    #[AsEventListener(event: 'elastic_entity.pre_update', method: 'onPreUpdate')]
    public function onPreUpdate(ElasticEntityEvent $event): void
    {
        // Logic before updating an entity
    }

    #[AsEventListener(event: 'elastic_entity.post_update', method: 'onPostUpdate')]
    public function onPostUpdate(ElasticEntityEvent $event): void
    {
        // Logic after updating an entity
    }

    #[AsEventListener(event: 'elastic_entity.pre_remove', method: 'onPreRemove')]
    public function onPreRemove(ElasticEntityEvent $event): void
    {
        // Logic before removing an entity
    }

    #[AsEventListener(event: 'elastic_entity.post_remove', method: 'onPostRemove')]
    public function onPostRemove(ElasticEntityEvent $event): void
    {
        // Logic after removing an entity
    }
}
