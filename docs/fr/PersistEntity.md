### Persistance des Entités

#### Description
Persister une entité la prépare à être insérée dans l'index Elasticsearch. L'entité doit implémenter `ElasticEntityInterface` et posséder un ID valide.

#### Exemple
```php
use App\Entity\Product;
use Zhortein\ElasticEntityBundle\Manager\ElasticEntityManager;

$product = new Product();
$product->setId('123');
$product->setName('Produit Exemple');

$entityManager = new ElasticEntityManager($client, $metadataCollector, $eventDispatcher);
$entityManager->persist($product);
$entityManager->flush();
```

--- 

[Retour](./FEATURES_DOCUMENTATION.md)