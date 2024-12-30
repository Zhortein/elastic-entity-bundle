### Définir une Entité

#### Description
Les entités sont définies à l'aide des attributs PHP (`#[...]`) pour décrire leur mapping vers Elasticsearch. Ces attributs spécifient l'index, les champs et les relations de l'entité. Chaque entité doit :
- Implémenter l'interface `ElasticEntityInterface`.
- Utiliser le trait `ElasticEntityTrait` pour le comportement commun.

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

- **index** : Le nom de l'index Elasticsearch.
- **shards** : Nombre de shards principaux.
- **replicas** : Nombre de shards répliqués.
- **refreshInterval** : Intervalle de rafraîchissement pour l'index.
- **settings** : Paramètres supplémentaires pour l'index.

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
- **analyzer** : Analyseur à utiliser pour le champ.
- **nullable** : Si le champ peut être null.
- **directives** : Options supplémentaires pour le champ.

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

- **targetClass** : La classe de l'entité liée.
- **type** : Type de relation (`nested` ou `reference`).
    - `reference`: Stockez et récupérez des entités via leur ID.
    - `nested`: Intégrez des entités directement au sein de l'entité parente.

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