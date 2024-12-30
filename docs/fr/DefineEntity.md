### Définir une Entité

#### Description
Les entités sont définies en utilisant des attributs PHP (`#[...]`) pour décrire leur mapping avec Elasticsearch. Ces attributs spécifient l'index, les champs et les relations de l'entité. Chaque entité doit :
- Implémenter l'interface `ElasticEntityInterface`.
- Utiliser le trait `ElasticEntityTrait` pour les comportements communs.

#### Attribut ElasticEntity
Définit la configuration de l'index Elasticsearch pour l'entité.

```php
use Zhortein\ElasticEntityBundle\Attribute\ElasticEntity;

#[ElasticEntity(
    index: 'products',
    shards: 1,
    replicas: 1,
    refreshInterval: '1s',
    settings: ['number_of_routing_shards' => 1]
)]
class Product
{
    // ...
}
```

- **index** : Nom de l'index Elasticsearch.
- **shards** : Nombre de shards primaires.
    - **Concept** : Les shards primaires sont des subdivisions d'un index. Elles permettent le traitement parallèle et améliorent les performances de recherche.
- **replicas** : Nombre de shards de réplication.
    - **Concept** : Les shards de réplication sont des copies des shards primaires. Elles permettent la tolérance aux pannes et augmentent la capacité de lecture.
- **refreshInterval** : Intervalle de rafraîchissement de l'index (par exemple, `1s` pour une seconde).
    - **Concept** : Contrôle la fréquence à laquelle Elasticsearch rend les modifications visibles pour les recherches. Des valeurs plus longues réduisent les coûts en écriture.
- **settings** : Paramètres supplémentaires pour la configuration de l'index (par exemple, `number_of_routing_shards` pour optimiser le routage).

#### Attribut ElasticField
Définit un champ dans le document Elasticsearch.

```php
use Zhortein\ElasticEntityBundle\Attribute\ElasticField;

#[ElasticField(
    type: 'text',
    analyzer: 'standard',
    nullable: false
)]
private string $name;
```

- **type** : Type de champ (par exemple, `text`, `keyword`, `integer`).
    - **Concept** : `text` est utilisé pour les données analysées, comme les descriptions, tandis que `keyword` est utilisé pour les données non analysées, comme les identifiants uniques.
- **analyzer** : Analyseur utilisé pour le champ.
    - **Concept** : Les analyseurs décomposent le texte en tokens pour permettre des recherches efficaces. Exemple : l'analyseur `standard` divise le texte par mots.
- **nullable** : Indique si le champ peut être nul.
- **directives** : Options supplémentaires pour le champ (par exemple, spécifications de format ou contraintes).

#### Attribut ElasticRelation
Définit une relation avec une autre entité.

```php
use Zhortein\ElasticEntityBundle\Attribute\ElasticRelation;

#[ElasticRelation(
    targetClass: Category::class,
    type: 'nested'
)]
private Category $category;
```

- **targetClass** : Classe de l'entité liée.
- **type** : Type de relation (`nested` ou `reference`).
    - **`reference`** : Stocke et récupère les entités via leur identifiant.
        - **Concept** : Permet de référencer des entités sans dupliquer leurs données, réduisant ainsi l'espace disque.
    - **`nested`** : Imbrique directement les entités au sein de l'entité parente.
        - **Concept** : Idéal pour représenter des structures hiérarchiques complexes, mais peut entraîner des coûts de recherche plus élevés.

#### Validation des Entités
Les entités peuvent inclure des contraintes pour valider leurs données en utilisant les contraintes Symfony Validator.

##### Exemple avec des Contraintes
```php
use Symfony\Component\Validator\Constraints as Assert;
use Zhortein\ElasticEntityBundle\Attribute\ElasticEntity;
use Zhortein\ElasticEntityBundle\Attribute\ElasticField;
use Zhortein\ElasticEntityBundle\Contracts\ElasticEntityInterface;
use Zhortein\ElasticEntityBundle\Traits\ElasticEntityTrait;

#[ElasticEntity(index: 'products')]
class Product implements ElasticEntityInterface
{
    use ElasticEntityTrait;

    #[ElasticField(type: 'keyword', nullable: false)]
    #[Assert\NotBlank]
    private string $id;

    #[ElasticField(type: 'text', analyzer: 'standard')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    private string $name;

    #[ElasticField(type: 'float')]
    #[Assert\Positive]
    private float $price;

    // Getters et setters...
}
```

- **@Assert\NotBlank** : Garantit que la valeur n'est pas vide.
- **@Assert\Length(min, max)** : Définit la longueur minimale et maximale.
- **@Assert\Positive** : Vérifie que la valeur est positive.

#### Exemple Complet

```php
use Zhortein\ElasticEntityBundle\Attribute\ElasticEntity;
use Zhortein\ElasticEntityBundle\Attribute\ElasticField;
use Zhortein\ElasticEntityBundle\Attribute\ElasticRelation;
use Zhortein\ElasticEntityBundle\Contracts\ElasticEntityInterface;
use Zhortein\ElasticEntityBundle\Traits\ElasticEntityTrait;

#[ElasticEntity(index: 'products')]
class Product implements ElasticEntityInterface
{
    use ElasticEntityTrait;

    #[ElasticField(type: 'keyword', nullable: false)]
    private string $id;

    #[ElasticField(type: 'text', analyzer: 'standard')]
    private string $name;

    #[ElasticRelation(targetClass: Category::class, type: 'nested')]
    private Category $category;

    // Getters et setters...
}
```

---

[Retour](./FEATURES_DOCUMENTATION.md)
