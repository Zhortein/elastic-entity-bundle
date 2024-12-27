# Features Documentation

## English Documentation

A [French version](./FEATURES_DOCUMENTATION_fr.md) is also available.

### Overview

The `ElasticEntityManager` provides a way to handle entities stored in an Elasticsearch backend with a Doctrine-like interface. It simplifies interaction with Elasticsearch by offering methods for persisting, updating, deleting, and querying entities.

---

### Table of Contents
- [Defining an Entity](#defining-an-entity)
- [Persisting Entities](#persisting-entities)
- [Updating Entities](#updating-entities)
- [Removing Entities](#removing-entities)
- [Flushing Operations](#flushing-operations)
- [Querying Entities](#querying-entities)
  - [Finding by ID](#finding-by-id)
  - [Finding with Criteria](#finding-with-criteria)
  - [Aggregation Queries](#aggregation-queries)
- [Event System](#event-system)
- [Snapshots and Change Detection](#snapshots-and-change-detection)

---

### Defining an Entity

#### Description
Entities are defined using PHP attributes (`#[...]`) to describe their mapping to Elasticsearch. These attributes specify the index, fields, and relationships of the entity. Each entity must:
- Implement the `ElasticEntityInterface`.
- Use the `ElasticEntityTrait` for common behavior.

#### ElasticEntity Attribute
Defines the Elasticsearch index configuration for the entity.

```php
use Zhortein\ElasticEntityBundle\Attribute\ElasticEntity;

#[ElasticEntity(
    index: 'products',
    shards: 1,
    replicas: 1,
    refreshInterval: '1s',
    settings: ['number_of_routing_shards' => 1]
)]
class Product
{
    // ...
}
```

- **index**: The name of the Elasticsearch index.
- **shards**: Number of primary shards.
- **replicas**: Number of replica shards.
- **refreshInterval**: Refresh interval for the index.
- **settings**: Additional index settings.

#### ElasticField Attribute
Defines a field in the Elasticsearch document.

```php
use Zhortein\ElasticEntityBundle\Attribute\ElasticField;

#[ElasticField(
    type: 'text',
    analyzer: 'standard',
    nullable: false
)]
private string $name;
```

- **type**: Field type (e.g., `text`, `keyword`, `integer`).
- **analyzer**: Analyzer to use for the field.
- **nullable**: Whether the field can be null.
- **directives**: Additional field options.

#### ElasticRelation Attribute
Defines a relationship to another entity.

```php
use Zhortein\ElasticEntityBundle\Attribute\ElasticRelation;

#[ElasticRelation(
    targetClass: Category::class,
    type: 'nested'
)]
private Category $category;
```

- **targetClass**: The related entity class.
- **type**: Type of relation (`nested` or `reference`).
  - `reference`: Store and retrieve entities by ID.
  - `nested`: Embed entities directly within the parent entity.

#### Complete Example

```php
use Zhortein\ElasticEntityBundle\Attribute\ElasticEntity;
use Zhortein\ElasticEntityBundle\Attribute\ElasticField;
use Zhortein\ElasticEntityBundle\Attribute\ElasticRelation;
use Zhortein\ElasticEntityBundle\Contracts\ElasticEntityInterface;
use Zhortein\ElasticEntityBundle\Traits\ElasticEntityTrait;

#[ElasticEntity(index: 'products')]
class Product implements ElasticEntityInterface
{
    use ElasticEntityTrait;

    #[ElasticField(type: 'keyword', nullable: false)]
    private string $id;

    #[ElasticField(type: 'text', analyzer: 'standard')]
    private string $name;

    #[ElasticRelation(targetClass: Category::class, type: 'nested')]
    private Category $category;

    // Getters and setters...
}
```

---

### Persisting Entities

#### Description
Persisting an entity prepares it for insertion into the Elasticsearch index. The entity must implement `ElasticEntityInterface` and have a valid ID.

#### Example
```php
use App\Entity\Product;
use Zhortein\ElasticEntityBundle\Manager\ElasticEntityManager;

$product = new Product();
$product->setId('123');
$product->setName('Sample Product');

$entityManager = new ElasticEntityManager($client, $metadataCollector, $eventDispatcher);
$entityManager->persist($product);
$entityManager->flush();
```

---

### Updating Entities

#### Description
Updates are automatically detected based on changes in the entity's state compared to its snapshot. Only the modified fields are sent to Elasticsearch.

#### Example
```php
$product = $entityManager->find(Product::class, '123');
$product->setName('Updated Product');

$entityManager->flush(); // Updates the name field in Elasticsearch.
```

---

### Removing Entities

#### Description
Marks an entity for deletion from Elasticsearch.

#### Example
```php
$entityManager->remove($product);
$entityManager->flush();
```

---

### Flushing Operations

#### Description
The `flush` method synchronizes all pending operations (persist, update, delete) with Elasticsearch.

#### Example
```php
$entityManager->persist($product1);
$entityManager->remove($product2);
$entityManager->flush(); // Executes all pending operations.
```

---

### Querying Entities

#### Finding by ID

```php
$product = $entityManager->find(Product::class, '123');
if ($product) {
    echo $product->getName();
}
```

#### Finding with Criteria

```php
$product = $entityManager->findOneBy(Product::class, ['category' => 'electronics'], ['price' => 'asc']);
echo $product->getName();

$products = $entityManager->findBy(Product::class, ['category' => 'electronics'], ['price' => 'asc'], 10);
foreach ($products as $product) {
    echo $product->getName();
}
```

#### Aggregation Queries

```php
$aggregations = [
    'price_avg' => ['avg' => ['field' => 'price']]
];
$result = $entityManager->aggregate(Product::class, $aggregations);
echo $result['price_avg']['value'];
```

---

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

### Snapshots and Change Detection

#### Snapshots
A snapshot captures the state of an entity when it is first managed. This snapshot is used to detect changes.

#### Change Detection
Changes are detected automatically before `flush` is called. Only modified fields are included in the update operation.
