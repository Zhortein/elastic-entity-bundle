<?php

namespace Zhortein\ElasticEntityBundle\Tests\Fixtures;

use Zhortein\ElasticEntityBundle\Attribute\ElasticEntity;
use Zhortein\ElasticEntityBundle\Attribute\ElasticField;
use Zhortein\ElasticEntityBundle\Contracts\ElasticEntityInterface;
use Zhortein\ElasticEntityBundle\Traits\ElasticEntityTrait;

#[ElasticEntity(index: 'dummy_index')]
class DummyEntity implements ElasticEntityInterface
{
    use ElasticEntityTrait;

    private string $name;

    #[ElasticField(type: 'text', analyzer: 'custom_analyzer', directives: ['exclude' => false])]
    private string $field1 = '';

    #[ElasticField(type: 'integer')]
    private int $field2 = 0;

    public function __construct(string $id = '1', string $name = 'Test')
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getField1(): string
    {
        return $this->field1;
    }

    public function setField1(string $field1): self
    {
        $this->field1 = $field1;

        return $this;
    }

    public function getField2(): int
    {
        return $this->field2;
    }

    public function setField2(int $field2): self
    {
        $this->field2 = $field2;

        return $this;
    }
}
