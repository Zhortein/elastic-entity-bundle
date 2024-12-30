### Event System

The `ElasticEntityManager` triggers events during the lifecycle of an entity. Developers can hook into these events:
- `elastic_entity.pre_persist`
- `elastic_entity.post_persist`
- `elastic_entity.pre_update`
- `elastic_entity.post_update`
- `elastic_entity.pre_remove`
- `elastic_entity.post_remove`

#### Example Listener
```php
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Zhortein\ElasticEntityBundle\Event\ElasticEntityEvent;

#[AsEventListener(event: 'elastic_entity.pre_persist')]
public function onPrePersist(ElasticEntityEvent $event): void
{
    $entity = $event->getEntity();
    // Custom logic before persisting
}
```

---

[Back](./FEATURES_DOCUMENTATION.md)
