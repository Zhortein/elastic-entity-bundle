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

[Back](./FEATURES_DOCUMENTATION.md)
