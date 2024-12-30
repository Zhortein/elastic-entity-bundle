### Recherche d'Entités

#### Recherche par ID

La méthode `find` permet de rechercher une entité spécifique à partir de son ID unique. Cette méthode retourne l'entité correspondante si elle est trouvée, ou `null` sinon.

##### Exemple
```php
$product = $entityManager->find(Product::class, '123');
if ($product) {
    echo $product->getName();
}
```

#### Recherche avec des Critères

Les méthodes `findBy` et `findOneBy` permettent de rechercher des entités basées sur des critères spécifiques. Vous pouvez également ajouter des critères de tri, une limite pour le nombre de résultats, et un décalage.

##### `findOneBy`
Cette méthode retourne la première entité correspondant aux critères spécifiés.

###### Exemple
```php
$product = $entityManager->findOneBy(Product::class, ['category' => 'electronics'], ['price' => 'asc']);
echo $product->getName();
```

##### `findBy`
Cette méthode retourne une liste d'entités correspondant aux critères.

###### Exemple
```php
$products = $entityManager->findBy(Product::class, ['category' => 'electronics'], ['price' => 'asc'], 10);
foreach ($products as $product) {
    echo $product->getName();
}
```

##### Critères Avancés
Vous pouvez utiliser des expressions Elasticsearch dans vos critères, telles que `_range`, `_prefix`, ou `_wildcard`.

###### Exemple avec des critères avancés
```php
$criteria = [
    'price' => ['_range' => ['gte' => 100, 'lte' => 500]],
    'name' => ['_wildcard' => '*Pro*']
];

$products = $entityManager->findBy(Product::class, $criteria, ['price' => 'asc']);
```

#### Requêtes d'Agrégation

La méthode `aggregate` permet d'exécuter des requêtes d'agrégation Elasticsearch pour obtenir des statistiques ou regrouper des données.

##### Exemple
```php
$aggregations = [
    'price_avg' => ['avg' => ['field' => 'price']]
];
$result = $entityManager->aggregate(Product::class, $aggregations);
echo $result['price_avg']['value'];
```

##### Combinaison avec des Critères
Vous pouvez combiner des agrégations avec des critères de recherche pour des analyses plus ciblées.

###### Exemple
```php
$criteria = ['category' => 'electronics'];
$aggregations = [
    'max_price' => ['max' => ['field' => 'price']]
];

$result = $entityManager->aggregate(Product::class, $aggregations, $criteria);
echo $result['max_price']['value'];
```
---

[Retour](./FEATURES_DOCUMENTATION.md)
