### Querying Entities

#### Finding by ID

```php
$product = $entityManager->find(Product::class, '123');
if ($product) {
    echo $product->getName();
}
```

#### Finding with Criteria

```php
$product = $entityManager->findOneBy(Product::class, ['category' => 'electronics'], ['price' => 'asc']);
echo $product->getName();

$products = $entityManager->findBy(Product::class, ['category' => 'electronics'], ['price' => 'asc'], 10);
foreach ($products as $product) {
    echo $product->getName();
}
```

#### Aggregation Queries

```php
$aggregations = [
    'price_avg' => ['avg' => ['field' => 'price']]
];
$result = $entityManager->aggregate(Product::class, $aggregations);
echo $result['price_avg']['value'];
```
---

[Back](./FEATURES_DOCUMENTATION.md)
