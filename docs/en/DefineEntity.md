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

[Back](./FEATURES_DOCUMENTATION.md)
