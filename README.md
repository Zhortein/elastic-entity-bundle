# ElasticEntityBundle

**English** | [Français](#fran%C3%A7ais)

---

## Introduction

The **ElasticEntityBundle** allows you to manage entities stored in Elasticsearch with functionality similar to Doctrine for relational databases.

### Key Features:
- **Entity-like Management**: Use PHP attributes to define `ElasticEntity` and `ElasticField` for mapping Elasticsearch indexes and fields and `ElasticRelation` for mapping relations between ElasticEntity.
- **Relations Support**: Handle `reference` and `nested` relations between entities.
- Optimized CRUD operations (Create, Read, Update, Delete).
- Support for advanced searches with filters and criteria.
- **Pagination**: Perform advanced searches with support for paginated results.
- Execution of Elasticsearch aggregations.

Compatible with **Symfony 7** and **PHP >= 8.3**.

---

## Installation

Add the bundle to your Symfony project via Composer:

```bash
composer require zhortein/elastic-entity-bundle
```

Enable the bundle in `config/bundles.php` if not automatically added:

```php
Zhortein\ElasticEntityBundle\ZhorteinElasticEntityBundle::class => ['all' => true],
```

---

## Usage

### Configuration of Entities

Entities are defined with the `ElasticEntity` attribute, and their fields are annotated with the `ElasticField` attribute for customization. Example:

```php
use Zhortein\ElasticEntityBundle\Attribute\ElasticEntity;
use Zhortein\ElasticEntityBundle\Attribute\ElasticField;

#[ElasticEntity(index: 'products', shards: 1, replicas: 1, refreshInterval: '1s')]
class Product
{
    #[ElasticField(type: 'text', analyzer: 'custom_analyzer')]
    private string $name;

    #[ElasticField(type: 'integer')]
    private int $price;

    // Getters and setters...
}
```

### Example Usage

```php
use Zhortein\ElasticEntityBundle\Manager\ElasticEntityManager;

// Inject ElasticEntityManager into your service
$manager->persist($product);
$manager->flush();

$product = $manager->find(Product::class, '123');

$products = $manager->findBy(Product::class, ['price' => ['_range' => ['gte' => 100, 'lte' => 500]]]);

$aggregations = $manager->aggregate(Product::class, [
    'price_avg' => [
        'avg' => ['field' => 'price'],
    ],
]);
```

For detailed functionality and examples, see the [Features Documentation](docs/FEATURES_DOCUMENTATION_en.md).

---

# Français

---

## Introduction

Le **ElasticEntityBundle** vous permet de gérer des entités stockées dans Elasticsearch, avec une fonctionnalité similaire à Doctrine pour les bases de données relationnelles.

### Fonctionnalités principales :
- **Gestion des Entités** : Utilisez des attributs PHP pour définir `ElasticEntity` et `ElasticField` pour mapper les index et champs Elasticsearch, et `ElasticRelation` pour mapper les relations entre les ElasticEntity.
- **Support des Relations** : Gérez les relations `reference` et `nested` entre les entités.
- Opérations CRUD optimisées (create, read, update, delete).
- Support des recherches avancées avec filtres et critères.
- **Pagination** : Effectuez des recherches avancées avec support pour des résultats paginés.
- Exécution d'agrégations Elasticsearch.

Compatible avec **Symfony 7** et **PHP >= 8.3**.

---

## Installation

Ajoutez le bundle à votre projet Symfony via Composer :

```bash
composer require zhortein/elastic-entity-bundle
```

Activez le bundle dans `config/bundles.php` si ce n'est pas fait automatiquement :

```php
Zhortein\ElasticEntityBundle\ZhorteinElasticEntityBundle::class => ['all' => true],
```

---

## Utilisation

### Configuration des Entités

Les entités sont définies avec l'attribut `ElasticEntity`, et leurs champs sont annotés avec l'attribut `ElasticField` pour personnalisation. Exemple :

```php
use Zhortein\ElasticEntityBundle\Attribute\ElasticEntity;
use Zhortein\ElasticEntityBundle\Attribute\ElasticField;

#[ElasticEntity(index: 'products', shards: 1, replicas: 1, refreshInterval: '1s')]
class Product
{
    #[ElasticField(type: 'text', analyzer: 'custom_analyzer')]
    private string $name;

    #[ElasticField(type: 'integer')]
    private int $price;

    // Getters et setters...
}
```

### Exemple d'utilisation

```php
use Zhortein\ElasticEntityBundle\Manager\ElasticEntityManager;

// Injectez ElasticEntityManager dans votre service
$manager->persist($product);
$manager->flush();

$product = $manager->find(Product::class, '123');

$products = $manager->findBy(Product::class, ['price' => ['_range' => ['gte' => 100, 'lte' => 500]]]);

$aggregations = $manager->aggregate(Product::class, [
    'price_avg' => [
        'avg' => ['field' => 'price'],
    ],
]);
```

Pour un détail des fonctionnalités et des exemples, consultez la [Documentation des Fonctionnalités](docs/FEATURES_DOCUMENTATION_fr.md).
