### Mise à Jour des Entités

#### Description
Les mises à jour sont automatiquement détectées en fonction des changements dans l'état de l'entité par rapport à son snapshot. Seuls les champs modifiés sont envoyés à Elasticsearch.

#### Exemple
```php
$product = $entityManager->find(Product::class, '123');
$product->setName('Produit Mis à Jour');

$entityManager->flush(); // Met à jour le champ "name" dans Elasticsearch.
```

--- 

[Retour](./FEATURES_DOCUMENTATION.md)