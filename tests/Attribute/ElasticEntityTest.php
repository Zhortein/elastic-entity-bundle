<?php

namespace Zhortein\ElasticEntityBundle\Tests\Attribute;

use PHPUnit\Framework\TestCase;
use Zhortein\ElasticEntityBundle\Attribute\ElasticEntity;

class ElasticEntityTest extends TestCase
{
    public function testItCreatesAnElasticEntityAttribute(): void
    {
        $attribute = new ElasticEntity(index: 'products');
        $this->assertEquals('products', $attribute->index);
    }
}
