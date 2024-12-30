### Requêtes Personnalisées

#### Description
`ElasticEntityManager` offre la possibilité d'exécuter des requêtes personnalisées sur Elasticsearch, que ce soit pour récupérer des résultats bruts ou des entités hydratées. Cette fonctionnalité permet une flexibilité accrue pour des cas d'utilisation avancés ou spécifiques.

---

#### Requête Personnalisée : `executeCustomQuery`

##### Description
Cette méthode permet d'exécuter une requête personnalisée sur Elasticsearch. Les résultats peuvent être retournés sous forme de tableaux bruts ou d'entités hydratées.

##### Paramètres
- `className` (nullable, `string`): Nom complet de la classe d'entité pour hydrater les résultats. Si `null`, des tableaux bruts sont retournés.
- `query` (`array<string, mixed>`): La requête Elasticsearch personnalisée.
- `options` (`array<string, mixed>`): Options supplémentaires, comme `index`, `size`, `from`, ou `sort`.

##### Exemple : Résultats Bruts
```php
use Zhortein\ElasticEntityBundle\Manager\ElasticEntityManager;

$query = [
    'query' => [
        'match_all' => []
    ]
];
$options = ['index' => 'products'];

$results = $entityManager->executeCustomQuery(null, $query, $options);

foreach ($results as $result) {
    echo $result['_source']['field_name'];
}
```

##### Exemple : Résultats Hydratés
```php
use App\Entity\Product;

$query = [
    'query' => [
        'match' => ['category' => 'electronics']
    ]
];

$results = $entityManager->executeCustomQuery(Product::class, $query);

foreach ($results as $product) {
    echo $product->getName();
}
```

---

#### Requête de Comptage : `countCustomQuery`

##### Description
Cette méthode permet d'exécuter une requête de comptage sur un index Elasticsearch.

##### Paramètres
- `index` (`string`): L'index Elasticsearch à interroger.
- `query` (`array<string, mixed>`): La requête Elasticsearch personnalisée.

##### Exemple :
```php
use Zhortein\ElasticEntityBundle\Manager\ElasticEntityManager;

$query = [
    'query' => [
        'term' => [
            'status' => 'active'
        ]
    ]
];

$count = $entityManager->countCustomQuery('products', $query);

echo "Nombre d'entités actives : $count";
```

---

### Configuration

Les requêtes personnalisées sont intégrées directement dans le `ElasticEntityManager` et ne nécessitent pas de configuration supplémentaire.

--- 

[Retour](./FEATURES_DOCUMENTATION.md)