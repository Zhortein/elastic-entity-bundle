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

[Retour](./FEATURES_DOCUMENTATION.md)