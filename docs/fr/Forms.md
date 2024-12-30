### Intégration des formulaires Symfony

#### Description
`ElasticEntityFormType` permet d'utiliser des entités Elasticsearch dans les formulaires Symfony de manière transparente. Les champs et relations sont automatiquement mappés en fonction des attributs `ElasticField` et `ElasticRelation`.

En outre, vous pouvez configurer les champs dynamiquement à l'aide de la propriété `directives` des attributs ou en personnalisant le comportement via le service `FormFieldConfigurator`.

---

#### Types de champs pris en charge

Les types Elasticsearch suivants sont mappés par défaut aux types de champs de formulaire Symfony :

| Type Elasticsearch | Type de champ Symfony |
|---------------------|------------------------|
| `text`, `keyword`  | `TextType`            |
| `integer`, `float`, `double` | `NumberType` |
| `nested`, `date_range` | `CollectionType` |
| `geo_point`         | `TextType` (pour l'instant) |

---

#### Personnalisation du comportement des champs

Vous pouvez personnaliser le comportement des champs dynamiquement à l'aide de la propriété `directives` dans l'attribut `ElasticField`. Ces directives sont transmises comme options au champ du formulaire.

##### Exemple : Directives personnalisées
```php
#[ElasticField(type: 'geo_point', directives: ['attr' => ['placeholder' => 'Saisir les coordonnées']])]
private string $location;
```

Vous pouvez également centraliser la personnalisation à l'aide du service `FormFieldConfigurator`, qui ajuste dynamiquement les options des champs.

##### Exemple : Personnalisation dynamique
```php
use Zhortein\ElasticEntityBundle\Service\FormFieldConfigurator;

class MonFormConfigurator extends FormFieldConfigurator
{
    public function configureFieldOptions(string $type, array $directives): array
    {
        if ($type === 'geo_point') {
            $directives['attr']['placeholder'] = 'Saisir les coordonnées (par exemple : "48.8566,2.3522")';
        }
        return $directives;
    }
}
```

---

#### Exemple d'utilisation

##### Exemple de formulaire avec une entité ElasticEntity
```php
use App\Entity\Product;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Zhortein\ElasticEntityBundle\Form\ElasticEntityFormType;

$formFactory = $this->get(FormFactoryInterface::class);

$product = new Product();
$product->setName('Produit exemple');

$form = $formFactory->create(ElasticEntityFormType::class, $product)
    ->add('submit', SubmitType::class, ['label' => 'Enregistrer']);

$formView = $form->createView();
```

---

### Configuration

#### Enregistrement automatique
`ElasticEntityFormType` est automatiquement enregistré dans votre application Symfony.

--- 

[Retour](./FEATURES_DOCUMENTATION.md)