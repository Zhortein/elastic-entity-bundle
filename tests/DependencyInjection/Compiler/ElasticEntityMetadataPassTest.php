<?php

namespace Zhortein\ElasticEntityBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Zhortein\ElasticEntityBundle\Attribute\ElasticEntity;
use Zhortein\ElasticEntityBundle\DependencyInjection\Compiler\ElasticEntityMetadataPass;
use Zhortein\ElasticEntityBundle\Metadata\MetadataCollector;

#[ElasticEntity(index: 'dummy')]
class DummyElasticEntity
{
}

class ElasticEntityMetadataPassTest extends TestCase
{
    public function testProcessRegistersElasticEntities(): void
    {
        $container = new ContainerBuilder();

        // Simulate MetadataCollector service
        $metadataCollectorDefinition = new Definition(MetadataCollector::class);
        $container->setDefinition(MetadataCollector::class, $metadataCollectorDefinition);

        // Manually register the DummyElasticEntity
        $dummyEntityDefinition = new Definition(DummyElasticEntity::class);
        $container->setDefinition(DummyElasticEntity::class, $dummyEntityDefinition);

        // Apply the compiler pass
        $compilerPass = new ElasticEntityMetadataPass();
        $compilerPass->process($container);

        // Verify that MetadataCollector received the correct metadata
        $calls = $metadataCollectorDefinition->getMethodCalls();
        $this->assertCount(1, $calls);
        $this->assertEquals('addMetadata', $calls[0][0]);
        $this->assertInstanceOf(\ReflectionClass::class, $calls[0][1][0]);
        $this->assertEquals(DummyElasticEntity::class, $calls[0][1][0]->getName());
    }
}
