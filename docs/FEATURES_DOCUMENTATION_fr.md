# Documentation des Fonctionnalités

## Documentation en Français

Une [version anglaise](./FEATURES_DOCUMENTATION_en.md) est également disponible.

### Aperçu

Le `ElasticEntityManager` fournit un moyen de gérer des entités stockées dans un backend Elasticsearch avec une interface similaire à Doctrine. Il simplifie les interactions avec Elasticsearch en offrant des méthodes pour persister, mettre à jour, supprimer et interroger des entités.

---

### Table des Matières
- [Définir une Entité](#définir-une-entité)
- [Persistance des Entités](#persistance-des-entités)
- [Mise à Jour des Entités](#mise-à-jour-des-entités)
- [Suppression des Entités](#suppression-des-entités)
- [Flush des Opérations](#flush-des-opérations)
- [Requête sur les Entités](#requête-sur-les-entités)
    - [Recherche par ID](#recherche-par-id)
    - [Recherche avec Critères](#recherche-avec-critères)
    - [Requêtes d'Agrégation](#requêtes-dagrégation)
- [Système d'Événements](#système-dévenements)
- [Snapshots et Détection des Changements](#snapshots-et-détection-des-changements)

---

### Définir une Entité

#### Description
Les entités sont définies à l'aide des attributs PHP (`#[...]`) pour décrire leur mapping vers Elasticsearch. Ces attributs spécifient l'index, les champs et les relations de l'entité. Chaque entité doit :
- Implémenter l'interface `ElasticEntityInterface`.
- Utiliser le trait `ElasticEntityTrait` pour le comportement commun.

#### Attribut ElasticEntity
Définit la configuration de l'index Elasticsearch pour l'entité.

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

- **index** : Le nom de l'index Elasticsearch.
- **shards** : Nombre de shards principaux.
- **replicas** : Nombre de shards répliqués.
- **refreshInterval** : Intervalle de rafraîchissement pour l'index.
- **settings** : Paramètres supplémentaires pour l'index.

#### Attribut ElasticField
Définit un champ dans le document Elasticsearch.

```php
use Zhortein\ElasticEntityBundle\Attribute\ElasticField;

#[ElasticField(
    type: 'text',
    analyzer: 'standard',
    nullable: false
)]
private string $name;
```

- **type** : Type de champ (par exemple, `text`, `keyword`, `integer`).
- **analyzer** : Analyseur à utiliser pour le champ.
- **nullable** : Si le champ peut être null.
- **directives** : Options supplémentaires pour le champ.

#### Attribut ElasticRelation
Définit une relation avec une autre entité.

```php
use Zhortein\ElasticEntityBundle\Attribute\ElasticRelation;

#[ElasticRelation(
    targetClass: Category::class,
    type: 'nested'
)]
private Category $category;
```

- **targetClass** : La classe de l'entité liée.
- **type** : Type de relation (`nested` ou `reference`).
    - `reference`: Stockez et récupérez des entités via leur ID.
    - `nested`: Intégrez des entités directement au sein de l'entité parente.

#### Exemple Complet

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

    // Getters et setters...
}
```

---

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

### Mise à Jour des Entités

#### Description
Les mises à jour sont automatiquement détectées en fonction des changements dans l'état de l'entité par rapport à son snapshot. Seuls les champs modifiés sont envoyés à Elasticsearch.

#### Exemple
```php
$product = $entityManager->find(Product::class, '123');
$product->setName('Produit Mis à Jour');

$entityManager->flush(); // Met à jour le champ "name" dans Elasticsearch.
```

---

### Suppression des Entités

#### Description
Marque une entité pour suppression dans Elasticsearch.

#### Exemple
```php
$entityManager->remove($product);
$entityManager->flush();
```

---

### Flush des Opérations

#### Description
La méthode `flush` synchronise toutes les opérations en attente (persistance, mise à jour, suppression) avec Elasticsearch.

#### Exemple
```php
$entityManager->persist($product1);
$entityManager->remove($product2);
$entityManager->flush(); // Exécute toutes les opérations en attente.
```

---

### Requête sur les Entités

#### Recherche par ID

```php
$product = $entityManager->find(Product::class, '123');
if ($product) {
    echo $product->getName();
}
```

#### Recherche avec Critères

```php
$product = $entityManager->findOneBy(Product::class, ['category' => 'electronics'], ['price' => 'asc']);
echo $product->getName();

$products = $entityManager->findBy(Product::class, ['category' => 'electronics'], ['price' => 'asc'], 10);
foreach ($products as $product) {
    echo $product->getName();
}
```

#### Requêtes d'Agrégation

```php
$aggregations = [
    'price_avg' => ['avg' => ['field' => 'price']]
];
$result = $entityManager->aggregate(Product::class, $aggregations);
echo $result['price_avg']['value'];
```

---

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

### Snapshots et Détection des Changements

#### Snapshots
Un snapshot capture l'état d'une entité lorsqu'elle est initialement gérée. Ce snapshot est utilisé pour détecter les changements.

#### Détection des Changements
Les changements sont détectés automatiquement avant l'appel à `flush`. Seuls les champs modifiés sont inclus dans l'opération de mise à jour.
