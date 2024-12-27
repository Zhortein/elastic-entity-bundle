# ElasticEntityBundle - Documentation des fonctionnalités

**English** | [Français](#français)

---

## English

### Overview
This document provides detailed information on the features of the **ElasticEntityBundle** and how to use them in your Symfony project.

---

### ElasticEntity Attribute

The `ElasticEntity` attribute defines an Elasticsearch index configuration for an entity.

#### Example:
```php
use Zhortein\ElasticEntityBundle\Attribute\ElasticEntity;

#[ElasticEntity(index: 'products', shards: 3, replicas: 2, refreshInterval: '1s')]
class Product
{
    // Fields...
}
```

#### Options:
- `index` (string): The name of the Elasticsearch index.
- `shards` (int|null): The number of shards for the index.
- `replicas` (int|null): The number of replicas for the index.
- `refreshInterval` (string|null): The refresh interval for the index.
- `settings` (array): Additional settings for advanced configurations.

---

### ElasticField Attribute

The `ElasticField` attribute customizes the mapping of entity fields in Elasticsearch.

#### Example:
```php
use Zhortein\ElasticEntityBundle\Attribute\ElasticField;

class Product
{
    #[ElasticField(type: 'text', analyzer: 'standard')]
    private string $name;

    #[ElasticField(type: 'integer')]
    private int $price;
}
```

#### Options:
- `type` (string): Field type (`text`, `keyword`, `integer`, etc.).
- `analyzer` (string|null): The analyzer for text fields.
- `directives` (array): Additional directives like `exclude` or dynamic settings.

---

### ElasticRelation Attribute

The `ElasticRelation` attribute allows to define relation with another ElasticEntity.

#### Example:
```php
use Zhortein\ElasticEntityBundle\Attribute\ElasticRelation;

class Product
{
    #[ElasticField(type: 'text', analyzer: 'standard')]
    private string $name;

    #[ElasticField(type: 'integer')]
    private int $price;
    
    #[ElasticRelation(type: 'reference', targetClass: Category::class))]
    private Category $category;
    
    /** @var Price[] $prices */
    #[ElasticRelation(type: 'nested', targetClass: Price::class)]
    private array $prices;
}
```

#### Options:
- `type` (string): Field type `reference` or `nested`.
  - `reference`: Store and retrieve entities by ID.
  - `nested`: Embed entities directly within the parent entity.
- `targetClass` (string): The target ElasticEntity class name

---

### Manager Methods

#### Persist an Entity
Saves or updates an entity in Elasticsearch.

```php
$manager->persist($product);
$manager->flush();
```

#### Remove an Entity
Deletes an entity by its ID.

```php
$manager->remove($product);
$manager->flush();
```

#### Find by ID
Fetches an entity by its ID.

```php
$product = $manager->find(Product::class, '123');
```

#### Advanced Search
Search entities with filters and criteria.

```php
$products = $manager->findBy(Product::class, [
    'price' => ['_range' => ['gte' => 100, 'lte' => 500]],
]);
$products = $manager->findOneBy(Product::class, [
    'price' => ['_range' => ['gte' => 100, 'lte' => 500]],
]);
```

#### Aggregations
Perform advanced aggregations.

```php
$aggregations = $manager->aggregate(Product::class, [
    'price_avg' => [
        'avg' => ['field' => 'price'],
    ],
]);
```

---

# Français

### Vue d'ensemble
Ce document fournit des informations détaillées sur les fonctionnalités du **ElasticEntityBundle** et leur utilisation dans votre projet Symfony.

---

### Attribut ElasticEntity

L'attribut `ElasticEntity` définit une configuration d'index Elasticsearch pour une entité.

#### Exemple :
```php
use Zhortein\ElasticEntityBundle\Attribute\ElasticEntity;

#[ElasticEntity(index: 'products', shards: 3, replicas: 2, refreshInterval: '1s')]
class Product
{
    // Champs...
}
```

#### Options :
- `index` (string) : Le nom de l'index Elasticsearch.
- `shards` (int|null) : Le nombre de shards pour l'index.
- `replicas` (int|null) : Le nombre de réplicas pour l'index.
- `refreshInterval` (string|null) : L'intervalle de rafraîchissement de l'index.
- `settings` (array) : Paramètres supplémentaires pour les configurations avancées.

---

### Attribut ElasticField

L'attribut `ElasticField` personnalise le mapping des champs d'une entité dans Elasticsearch.

#### Exemple :
```php
use Zhortein\ElasticEntityBundle\Attribute\ElasticField;

class Product
{
    #[ElasticField(type: 'text', analyzer: 'standard')]
    private string $name;

    #[ElasticField(type: 'integer')]
    private int $price;
}
```

#### Options :
- `type` (string) : Type du champ (`text`, `keyword`, `integer`, etc.).
- `analyzer` (string|null) : L'analyseur pour les champs texte.
- `directives` (array) : Directives supplémentaires comme `exclude` ou des réglages dynamiques.

---

### L'attribut ElasticRelation

L'attribut `ElasticRelation` permet de définir des relations avec un autre ElasticEntity.

#### Example:
```php
use Zhortein\ElasticEntityBundle\Attribute\ElasticRelation;

class Product
{
    #[ElasticField(type: 'text', analyzer: 'standard')]
    private string $name;

    #[ElasticField(type: 'integer')]
    private int $price;
    
    #[ElasticRelation(type: 'reference', targetClass: Category::class))]
    private Category $category;
    
    /** @var Price[] $prices */
    #[ElasticRelation(type: 'nested', targetClass: Price::class)]
    private array $prices;
}
```

#### Options :
- `type` (string): Type de relation `reference` or `nested`.
    - `reference`: Stockez et récupérez des entités via leur ID.
    - `nested`: Intégrez des entités directement au sein de l'entité parente.
- `targetClass` (string): Le nom de la classe ElasticEntity liée.

---

### Méthodes du Manager

#### Persister une Entité
Enregistre ou met à jour une entité dans Elasticsearch.

```php
$manager->persist($product);
$manager->flush();
```

#### Supprimer une Entité
Supprime une entité par son ID.

```php
$manager->remove($product);
$manager->flush();
```

#### Trouver par ID
Récupère une entité par son ID.

```php
$product = $manager->find(Product::class, '123');
```

#### Recherche Avancée
Recherchez des entités avec des filtres et des critères.

```php
$products = $manager->findBy(Product::class, [
    'price' => ['_range' => ['gte' => 100, 'lte' => 500]],
]);
```

#### Agrégations
Effectuez des agrégations avancées.

```php
$aggregations = $manager->aggregate(Product::class, [
    'price_avg' => [
        'avg' => ['field' => 'price'],
    ],
]);
