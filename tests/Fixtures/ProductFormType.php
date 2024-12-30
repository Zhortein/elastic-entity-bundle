<?php

namespace Zhortein\ElasticEntityBundle\Tests\Fixtures;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Zhortein\ElasticEntityBundle\Form\ElasticEntityFormType;

class ProductFormType extends ElasticEntityFormType
{
    protected function getEntityClass(): string
    {
        return Product::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        // Add custom fields or overrides for Product
        $builder->add('name', TextType::class, [
            'label' => 'Name',
            'required' => true,
            'attr' => ['placeholder' => 'Enter product name'],
        ]);
    }
}
