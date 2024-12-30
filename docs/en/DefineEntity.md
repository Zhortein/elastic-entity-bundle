### Defining an Entity

#### Description
Entities are defined using PHP attributes (`#[...]`) to describe their mapping to Elasticsearch. These attributes specify the index, fields, and relationships of the entity. Each entity must:
- Implement the `ElasticEntityInterface`.
- Use the `ElasticEntityTrait` for common behavior.

#### ElasticEntity Attribute
The `ElasticEntity` attribute defines the Elasticsearch index configuration for the entity.

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

- **`index`**: The name of the Elasticsearch index. It must be unique within your cluster.
- **`shards`**: Defines the number of primary shards for the index. Each shard is a self-contained piece of data that can be distributed across nodes. Increasing the shard count can improve indexing performance but might increase overhead.
- **`replicas`**: Specifies the number of replica shards. Replica shards provide fault tolerance by duplicating the data stored in primary shards.
- **`refreshInterval`**: Controls how often the index is refreshed, making recent changes searchable. A lower interval ensures up-to-date search results but can impact performance. Example: `'1s'` (every second).
- **`settings`**: Allows specifying additional configuration for the index. For example, `'number_of_routing_shards'` controls sharding during reindexing operations.

#### ElasticField Attribute
The `ElasticField` attribute defines individual fields in the Elasticsearch document.

```php
use Zhortein\ElasticEntityBundle\Attribute\ElasticField;

#[ElasticField(
    type: 'text',
    analyzer: 'standard',
    nullable: false,
    directives: ['boost' => 2.0]
)]
private string $name;
```

- **`type`**: Specifies the field type. Examples include:
    - `text`: Analyzed text field used for full-text search.
    - `keyword`: Exact match field used for filtering or sorting.
    - `integer`, `float`: Numeric fields for calculations.
- **`analyzer`**: Defines the analyzer used for processing text. Example:
    - `standard`: Default tokenizer with basic language support.
    - `english`: An English-specific analyzer for stemming and stop words.
- **`nullable`**: Indicates whether the field can be null. If `false`, validation will ensure the field is always set.
- **`directives`**: Additional directives for fine-tuning Elasticsearch behavior. Example:
    - `boost`: Increases the relevance score of a field during search queries.

#### ElasticRelation Attribute
The `ElasticRelation` attribute defines relationships between entities.

```php
use Zhortein\ElasticEntityBundle\Attribute\ElasticRelation;

#[ElasticRelation(
    targetClass: Category::class,
    type: 'nested'
)]
private Category $category;
```

- **`targetClass`**: Specifies the related entity class. The related entity must also implement `ElasticEntityInterface`.
- **`type`**: Specifies the relation type:
    - **`nested`**: The related entities are embedded directly within the parent entity as sub-documents. Useful for maintaining a hierarchical structure.
    - **`reference`**: Only the IDs of related entities are stored, and relationships are resolved dynamically. This approach minimizes data duplication but requires additional queries to retrieve related data.

#### Detailed Explanation of Key Concepts

##### Shards
- **Purpose**: Distributes data across multiple nodes in an Elasticsearch cluster for scalability.
- **Usage**: Use fewer shards for small datasets. Increase the number of shards for large datasets to optimize storage and search performance.
- **Example**: `shards: 3` creates three primary shards for the index.

##### Replicas
- **Purpose**: Provides redundancy and high availability by duplicating primary shards.
- **Usage**: At least one replica is recommended for production systems.
- **Example**: `replicas: 1` ensures one copy of each primary shard.

##### Refresh Interval
- **Purpose**: Controls how often data becomes searchable after being indexed.
- **Usage**: Set to `-1` for bulk indexing operations where immediate searchability is not required.
- **Example**: `refreshInterval: '30s'` refreshes the index every 30 seconds.

##### Analyzer
- **Purpose**: Defines how text fields are tokenized and normalized.
- **Usage**: Choose analyzers suited to the language and use case.
- **Example**: `analyzer: 'french'` applies language-specific rules for French text.

##### Nested Relations
- **Purpose**: Represents hierarchical or complex objects.
- **Usage**: Allows querying child objects independently within a parent entity.
- **Example**: Querying all products where the category name contains "electronics."

##### Reference Relations
- **Purpose**: Avoids duplication by storing only references to related entities.
- **Usage**: Use when relationships are dynamic or data duplication is a concern.
- **Example**: Storing user IDs instead of embedding full user profiles.

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
