# Custom Queries with ElasticEntityManager

The `ElasticEntityManager` provides functionality for executing custom queries against Elasticsearch indices, allowing advanced control over your data retrieval and manipulation.

[See all features](./FEATURES_DOCUMENTATION_en.md)

---

## Overview

Custom queries allow developers to execute tailored Elasticsearch requests, either for raw results or for hydrating results into entities. They also provide mechanisms to count entities based on specific query criteria.

### Methods for Custom Queries

#### 1. `executeCustomQuery`
Executes a custom query and optionally hydrates results into entities.

##### Parameters:
- `className` *(optional)*: The entity class to hydrate results into. If `null`, raw results are returned.
- `query`: An array representing the Elasticsearch query.
- `options` *(optional)*: Additional query options like `index`, `sort`, `size`, or `from`.

##### Returns:
- An array of hydrated entities or raw Elasticsearch results.

##### Example:
```php
// Retrieve raw results
$rawResults = $entityManager->executeCustomQuery(null, ['query' => ['match_all' => []]], ['index' => 'test_index']);

// Retrieve hydrated entities
$entities = $entityManager->executeCustomQuery(Product::class, ['query' => ['match' => ['category' => 'electronics']]]);
```

---

#### 2. `countCustomQuery`
Counts the number of documents matching the given query.

##### Parameters:
- `index`: The Elasticsearch index to query.
- `query`: An array representing the Elasticsearch query.

##### Returns:
- The count of matching documents as an integer.

##### Example:
```php
$count = $entityManager->countCustomQuery('test_index', ['query' => ['match_all' => []]]);
echo "Total documents: $count";
```

---

## Error Handling

### Common Exceptions

#### 1. Missing Index for Raw Results
If `className` is not provided, you must specify the `index` in `options`. Failure to do so will throw an exception:

```text
Index must be provided when no className is provided.
```

#### 2. Invalid Elasticsearch Responses
If the response from Elasticsearch is malformed or unexpected, the following exceptions may be thrown:

- For invalid aggregation response formats:
  ```text
  Invalid aggregation response format.
  ```

- For unexpected responses from count queries:
  ```text
  Unexpected response format from Elasticsearch count query.
  ```

---

## Best Practices

1. **Hydration Usage**:
    - Use the `className` parameter in `executeCustomQuery` to automatically map results to your entities.

2. **Index Specification**:
    - Always provide the `index` in the `options` array when `className` is not specified.

3. **Query Optimization**:
    - Leverage Elasticsearch-specific optimizations (e.g., filters, sort) directly in the `query` parameter.

4. **Error Handling**:
    - Wrap your calls in try-catch blocks to handle runtime exceptions and log unexpected responses for debugging.

---

[Back](./FEATURES_DOCUMENTATION.md)
