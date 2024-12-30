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

[Back](./FEATURES_DOCUMENTATION.md)
