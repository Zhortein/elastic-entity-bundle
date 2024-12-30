<?php

namespace Zhortein\ElasticEntityBundle\Tests\Metadata;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zhortein\ElasticEntityBundle\Metadata\MetadataCollector;

class MetadataCollectorTest extends TestCase
{
    public function testAddAndGetMetadata(): void
    {
        $cacheMock = $this->createMock(CacheInterface::class);

        $cacheMock->expects($this->exactly(2))
            ->method('get')
            ->willReturnCallback(function ($key, $callback) {
                return $callback($this->createMock(ItemInterface::class));
            });

        $collector = new MetadataCollector($cacheMock, $this->createMock(TranslatorInterface::class));

        $reflectionClass = new \ReflectionClass(self::class);
        $collector->addMetadata($reflectionClass);

        $metadata = $collector->getMetadata(self::class);
        $this->assertNotNull($metadata);
        $this->assertEquals(self::class, $metadata['class']);
        $this->assertEquals($reflectionClass->getAttributes(), $metadata['attributes']);
    }

    public function testGetAllMetadata(): void
    {
        $cacheMock = $this->createMock(CacheInterface::class);

        $collector = new MetadataCollector($cacheMock, $this->createMock(TranslatorInterface::class));

        $reflectionClass1 = new \ReflectionClass(self::class);
        $reflectionClass2 = new \ReflectionClass(MetadataCollector::class);

        $collector->addMetadata($reflectionClass1);
        $collector->addMetadata($reflectionClass2);

        $allMetadata = $collector->getAllMetadata();

        $this->assertCount(2, $allMetadata);
        $this->assertArrayHasKey(self::class, $allMetadata);
        $this->assertArrayHasKey(MetadataCollector::class, $allMetadata);
    }

    public function testClearMetadata(): void
    {
        // Use a real cache adapter for testing
        $cache = new ArrayAdapter();

        $collector = new MetadataCollector($cache, $this->createMock(TranslatorInterface::class));

        $reflectionClass = new \ReflectionClass(self::class);
        $collector->addMetadata($reflectionClass);

        $collector->clearMetadata();

        $allMetadata = $collector->getAllMetadata();

        $this->assertEmpty($allMetadata);
    }
}
