### Flush des Opérations

#### Description
La méthode `flush` synchronise toutes les opérations en attente (persistance, mise à jour, suppression) avec Elasticsearch.

#### Exemple
```php
$entityManager->persist($product1);
$entityManager->remove($product2);
$entityManager->flush(); // Exécute toutes les opérations en attente.
```

--- 

[Retour](./FEATURES_DOCUMENTATION.md)