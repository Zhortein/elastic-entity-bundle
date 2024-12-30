### Système d'Événements

Le `ElasticEntityManager` déclenche des événements au cours du cycle de vie d'une entité. Les développeurs peuvent se brancher sur ces événements :
- `elastic_entity.pre_persist`
- `elastic_entity.post_persist`
- `elastic_entity.pre_update`
- `elastic_entity.post_update`
- `elastic_entity.pre_remove`
- `elastic_entity.post_remove`

#### Exemple de Listener
```php
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Zhortein\ElasticEntityBundle\Event\ElasticEntityEvent;

#[AsEventListener(event: 'elastic_entity.pre_persist')]
public function onPrePersist(ElasticEntityEvent $event): void
{
    $entity = $event->getEntity();
    // Logique personnalisée avant la persistance
}
```

--- 

[Retour](./FEATURES_DOCUMENTATION.md)