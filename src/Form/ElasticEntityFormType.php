<?php

namespace Zhortein\ElasticEntityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zhortein\ElasticEntityBundle\Attribute\ElasticField;
use Zhortein\ElasticEntityBundle\Contracts\ElasticEntityInterface;
use Zhortein\ElasticEntityBundle\Service\FormFieldConfigurator;

/**
 * @template TData of object
 *
 * @extends AbstractType<TData>
 */
abstract class ElasticEntityFormType extends AbstractType
{
    abstract protected function getEntityClass(): string;

    public function __construct(private FormFieldConfigurator $fieldConfigurator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!isset($options['data'])) {
            throw new \InvalidArgumentException('The "data" option must be set and an instance of ElasticEntityInterface, but the "data" option is not set...');
        }

        if (!$options['data'] instanceof ElasticEntityInterface) {
            throw new \InvalidArgumentException(sprintf('The "data" option must be an instance of ElasticEntityInterface. Got: %s', is_object($options['data']) ? get_class($options['data']) : gettype($options['data'])));
        }

        /** @var ElasticEntityInterface $entity */
        $entity = $options['data'];

        $reflectionClass = new \ReflectionClass($entity);
        foreach ($reflectionClass->getProperties() as $property) {
            $attributes = $property->getAttributes(ElasticField::class);
            if (!empty($attributes)) {
                /** @var ElasticField $attribute */
                $attribute = $attributes[0]->newInstance();

                $fieldType = $this->getSymfonyFieldType($attribute->type);
                $directives = $this->fieldConfigurator->configureFieldOptions($attribute->type, $attribute->directives);

                $builder->add($property->getName(), $fieldType, $directives);
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->getEntityClass(),
            'data' => null,
        ]);
    }

    private function getSymfonyFieldType(string $type): string
    {
        $defaultType = TextType::class;

        return match ($type) {
            'text', 'keyword', 'geo_point' => TextType::class,
            'integer', 'float', 'double' => NumberType::class,
            'nested', 'date_range' => CollectionType::class,
            default => $defaultType,
        };
    }
}
