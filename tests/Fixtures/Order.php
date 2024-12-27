<?php

namespace Zhortein\ElasticEntityBundle\Tests\Fixtures;

use Zhortein\ElasticEntityBundle\Attribute\ElasticEntity;
use Zhortein\ElasticEntityBundle\Attribute\ElasticField;
use Zhortein\ElasticEntityBundle\Attribute\ElasticRelation;
use Zhortein\ElasticEntityBundle\Contracts\ElasticEntityInterface;
use Zhortein\ElasticEntityBundle\Traits\ElasticEntityTrait;

#[ElasticEntity(index: 'orders')]
class Order implements ElasticEntityInterface
{
    use ElasticEntityTrait;

    #[ElasticField(type: 'text')]
    private string $orderNumber;

    #[ElasticRelation(targetClass: Product::class, type: 'nested')]
    private array $products = [];

    #[ElasticRelation(targetClass: Customer::class, type: 'reference')]
    private ?Customer $customer = null;

    public function __construct()
    {
        $this->id = uniqid('', true);
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    public function getProducts(): array
    {
        return $this->products;
    }

    public function setProducts(array $products): void
    {
        $this->products = $products;
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer): void
    {
        $this->customer = $customer;
    }
}
