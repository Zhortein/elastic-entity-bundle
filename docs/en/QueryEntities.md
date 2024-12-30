### Querying Entities

The `ElasticEntityManager` provides several powerful methods to retrieve entities from Elasticsearch using various strategies.

---

#### Finding by ID

Retrieve a single entity by its unique identifier (ID). If the entity exists in Elasticsearch, it will be hydrated into an object.

##### Example
```php
$product = $entityManager->find(Product::class, '123');
if ($product) {
    echo 'Product Name: ' . $product->getName();
} else {
    echo 'No product found with the given ID.';
}
```

##### Notes
- If no entity is found with the specified ID, `null` is returned.
- Ensure the entity class has the required `getId` method for correct functionality.

---

#### Finding with Criteria

Search for entities by specifying field criteria. Use `findOneBy` to retrieve the first matching entity or `findBy` to retrieve multiple entities.

##### Example: `findOneBy`
Retrieve the first entity matching the given criteria.

```php
$product = $entityManager->findOneBy(
    Product::class,
    ['category' => 'electronics', 'availability' => 'in_stock'],
    ['price' => 'asc']
);
if ($product) {
    echo 'Cheapest product in electronics: ' . $product->getName();
}
```

##### Example: `findBy`
Retrieve multiple entities matching the given criteria.

```php
$products = $entityManager->findBy(
    Product::class,
    ['category' => 'electronics', 'availability' => 'in_stock'],
    ['price' => 'asc'],
    5,
    0
);

foreach ($products as $product) {
    echo 'Product Name: ' . $product->getName() . PHP_EOL;
}
```

##### Parameters
- **Criteria**: An associative array where the key is the field name and the value is the condition. Advanced Elasticsearch expressions (e.g., range, wildcard) are also supported.
- **Order By**: An associative array specifying the sorting field and direction (`asc` or `desc`).
- **Limit**: The maximum number of entities to return.
- **Offset**: The starting point for the query.

##### Advanced Criteria Examples
- Range Query:
```php
$criteria = ['price' => ['_range' => ['gte' => 100, 'lte' => 500]]];
```
- Wildcard Query:
```php
$criteria = ['name' => ['_wildcard' => 'Laptop*']];
```
- Prefix Query:
```php
$criteria = ['sku' => ['_prefix' => 'ABC']];
```

##### Notes
- When using advanced criteria, ensure field mappings in Elasticsearch support the specified queries.
- If no matches are found, an empty array is returned.

---

#### Aggregation Queries

Perform aggregation queries to calculate metrics or generate insights from data stored in Elasticsearch.

##### Example: Average Price
```php
$aggregations = [
    'price_avg' => ['avg' => ['field' => 'price']]
];
$result = $entityManager->aggregate(Product::class, $aggregations);
echo 'Average Price: ' . $result['price_avg']['value'];
```

##### Example: Count by Category
```php
$aggregations = [
    'categories_count' => [
        'terms' => ['field' => 'category.keyword']
    ]
];
$result = $entityManager->aggregate(Product::class, $aggregations);
foreach ($result['categories_count']['buckets'] as $bucket) {
    echo 'Category: ' . $bucket['key'] . ' - Count: ' . $bucket['doc_count'] . PHP_EOL;
}
```

##### Notes
- The aggregation response structure depends on the query definition.
- Ensure that the field used in the aggregation is correctly mapped in Elasticsearch.

---

[Back](./FEATURES_DOCUMENTATION.md)
