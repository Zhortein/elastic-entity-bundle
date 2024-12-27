<?php

namespace Zhortein\ElasticEntityBundle\Tests\Fixtures;

use Zhortein\ElasticEntityBundle\Attribute\ElasticEntity;
use Zhortein\ElasticEntityBundle\Attribute\ElasticField;
use Zhortein\ElasticEntityBundle\Contracts\ElasticEntityInterface;
use Zhortein\ElasticEntityBundle\Traits\ElasticEntityTrait;

#[ElasticEntity(index: 'customers')]
class Customer implements ElasticEntityInterface
{
    use ElasticEntityTrait;

    #[ElasticField(type: 'text')]
    private string $name;

    public function __construct()
    {
        $this->id = uniqid('', true);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
