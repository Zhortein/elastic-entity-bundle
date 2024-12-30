<?php

namespace Zhortein\ElasticEntityBundle\Tests\Form;

use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Zhortein\ElasticEntityBundle\Service\FormFieldConfigurator;
use Zhortein\ElasticEntityBundle\Tests\Fixtures\OrderFormType;
use Zhortein\ElasticEntityBundle\Tests\Fixtures\Product;
use Zhortein\ElasticEntityBundle\Tests\Fixtures\ProductFormType;

class ElasticEntityFormTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $formType = new ProductFormType(new FormFieldConfigurator());
        $formTypeOrder = new OrderFormType(new FormFieldConfigurator());

        return [
            new PreloadedExtension([$formType], []),
            new PreloadedExtension([$formTypeOrder], []),
        ];
    }

    public function testFormFieldsGeneratedForEntity(): void
    {
        $product = new Product();
        $product->setName('Sample Product');
        $product->setPrice(19.99);

        $form = $this->factory->create(ProductFormType::class, $product);

        $this->assertTrue($form->has('name'));
        $this->assertTrue($form->has('price'));
        $this->assertEquals('Sample Product', $form->get('name')->getData());
        $this->assertEquals(19.99, $form->get('price')->getData());
    }

    public function testFieldCustomizations(): void
    {
        $product = new Product();
        $form = $this->factory->create(ProductFormType::class, $product);

        $field = $form->get('name');
        $this->assertArrayHasKey('attr', $field->getConfig()->getOptions());
        $this->assertEquals('Enter product name', $field->getConfig()->getOption('attr')['placeholder']);
    }
}
