<?php

namespace Zhortein\ElasticEntityBundle\Tests\Fixtures;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Zhortein\ElasticEntityBundle\Form\ElasticEntityFormType;

class OrderFormType extends ElasticEntityFormType
{
    protected function getEntityClass(): string
    {
        return Order::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        // Add specific fields for Order
        $builder
            ->add('orderNumber', TextType::class, [
                'label' => 'Order Number',
                'required' => true,
                'attr' => ['placeholder' => 'Enter the order number'],
            ])
            ->add('products', CollectionType::class, [
                'entry_type' => ProductFormType::class,
                'entry_options' => [
                    'data_class' => Product::class, // Important !
                ],
                'label' => 'Products',
                'allow_add' => true,
                'allow_delete' => true,
            ]);
    }
}
