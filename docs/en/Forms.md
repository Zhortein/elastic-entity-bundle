### Symfony Forms Integration

#### Description
`ElasticEntityFormType` allows you to use Elasticsearch entities in Symfony forms seamlessly. It automatically maps fields and relations based on `ElasticField` and `ElasticRelation` attributes.

Additionally, you can configure fields dynamically using directives in the attributes or by customizing the behavior via the `FormFieldConfigurator` service.

---

#### Supported Field Types

The following Elasticsearch types are mapped to Symfony form field types by default:

| Elasticsearch Type | Symfony Field Type  |
|---------------------|---------------------|
| `text`, `keyword`  | `TextType`          |
| `integer`, `float`, `double` | `NumberType` |
| `nested`, `date_range` | `CollectionType` |
| `geo_point`         | `TextType` (for now) |

---

#### Customizing Field Behavior

You can customize field behavior dynamically using the `directives` property in the `ElasticField` attribute. These directives are passed as options to the form field.

##### Example: Custom Directives
```php
#[ElasticField(type: 'geo_point', directives: ['attr' => ['placeholder' => 'Enter coordinates']])]
private string $location;
```

You can also centralize customization using the `FormFieldConfigurator` service, which dynamically adjusts field options.

##### Example: Dynamic Customization
```php
use Zhortein\ElasticEntityBundle\Service\FormFieldConfigurator;

class MyFormConfigurator extends FormFieldConfigurator
{
    public function configureFieldOptions(string $type, array $directives): array
    {
        if ($type === 'geo_point') {
            $directives['attr']['placeholder'] = 'Enter coordinates (e.g., "48.8566,2.3522")';
        }
        return $directives;
    }
}
```

---

#### Example Usage

##### Example Form with ElasticEntity
```php
use App\Entity\Product;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Zhortein\ElasticEntityBundle\Form\ElasticEntityFormType;

$formFactory = $this->get(FormFactoryInterface::class);

$product = new Product();
$product->setName('Sample Product');

$form = $formFactory->create(ElasticEntityFormType::class, $product)
    ->add('submit', SubmitType::class, ['label' => 'Save']);

$formView = $form->createView();
```

---

### Configuration

#### Automatic Registration
`ElasticEntityFormType` is automatically registered in your Symfony application.

---

[Back](./FEATURES_DOCUMENTATION.md)
