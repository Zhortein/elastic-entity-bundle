### Updating Entities

#### Description
Updates are automatically detected based on changes in the entity's state compared to its snapshot. Only the modified fields are sent to Elasticsearch.

#### Example
```php
$product = $entityManager->find(Product::class, '123');
$product->setName('Updated Product');

$entityManager->flush(); // Updates the name field in Elasticsearch.
```

---

[Back](./FEATURES_DOCUMENTATION.md)
