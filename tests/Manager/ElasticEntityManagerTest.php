<?php

namespace Zhortein\ElasticEntityBundle\Tests\Manager;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zhortein\ElasticEntityBundle\Attribute\ElasticEntity;
use Zhortein\ElasticEntityBundle\Client\ClientWrapper;
use Zhortein\ElasticEntityBundle\Manager\ElasticEntityManager;
use Zhortein\ElasticEntityBundle\Metadata\MetadataCollector;
use Zhortein\ElasticEntityBundle\Tests\Fixtures\DummyEntity;

class ElasticEntityManagerTest extends TestCase
{
    public function testAggregate(): void
    {
        $clientMock = $this->createMock(ClientWrapper::class);
        $metadataCollectorMock = $this->createMock(MetadataCollector::class);
        $eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $validatorMock = $this->createMock(ValidatorInterface::class);

        $metadataCollectorMock->expects($this->once())
            ->method('getMetadata')
            ->with(DummyEntity::class)
            ->willReturn([
                'attributes' => [
                    $this->createElasticEntityAttributeMock('dummy_index', 1, 1, '1s', ['setting' => 'value']),
                ],
            ]);

        $clientMock->expects($this->once())
            ->method('search')
            ->with($this->callback(function ($params) {
                $this->assertEquals('dummy_index', $params['index']);
                $this->assertArrayHasKey('aggs', $params['body']);
                $this->assertArrayHasKey('price_avg', $params['body']['aggs']);

                return true;
            }))
            ->willReturn([
                'aggregations' => [
                    'price_avg' => ['value' => 42.5],
                ],
            ]);

        $manager = new ElasticEntityManager($clientMock, $metadataCollectorMock, $eventDispatcherMock, $validatorMock,
            $this->createMock(TranslatorInterface::class));

        $aggregations = [
            'price_avg' => [
                'avg' => [
                    'field' => 'price',
                ],
            ],
        ];

        $result = $manager->aggregate(DummyEntity::class, $aggregations);

        $this->assertArrayHasKey('price_avg', $result);
        $this->assertEquals(42.5, $result['price_avg']['value']);
    }

    public function testPersistUsesCorrectIndexAndFieldConfigurations(): void
    {
        $clientMock = $this->createMock(ClientWrapper::class);
        $metadataCollectorMock = $this->createMock(MetadataCollector::class);
        $eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $validatorMock = $this->createMock(ValidatorInterface::class);

        $metadataCollectorMock
            ->method('getMetadata')
            ->with(DummyEntity::class)
            ->willReturn([
                'attributes' => [
                    $this->createElasticEntityAttributeMock('dummy_index', 1, 1, '1s', ['setting' => 'value']),
                ],
            ]);

        $manager = new ElasticEntityManager($clientMock, $metadataCollectorMock, $eventDispatcherMock, $validatorMock,
            $this->createMock(TranslatorInterface::class));

        $entity = new DummyEntity();
        $entity->setField1('test value');

        $manager->persist($entity);

        $reflection = new \ReflectionClass($manager);
        $pendingOperations = $reflection->getProperty('pendingOperations');

        $operations = $pendingOperations->getValue($manager);
        $this->assertCount(1, $operations);
        $this->assertEquals('dummy_index', $operations[0]['index']['_index']);
        $this->assertArrayHasKey('field1', $operations[0]['data']);
        $this->assertEquals('test value', $operations[0]['data']['field1']);

        $entity = new DummyEntity();
        $entity->setField1('test value');

        $manager->persist($entity);

        $reflection = new \ReflectionClass($manager);
        $pendingOperations = $reflection->getProperty('pendingOperations');

        $operations = $pendingOperations->getValue($manager);
        $this->assertCount(1, $operations);
        $this->assertArrayHasKey('field1', $operations[0]['data']);
        $this->assertEquals('test value', $operations[0]['data']['field1']);
        $this->assertArrayHasKey('field2', $operations[0]['data']);
    }

    private function createElasticEntityAttributeMock(string $index, ?int $shards, ?int $replicas, ?string $refreshInterval, array $settings): \ReflectionAttribute
    {
        $mock = $this->createMock(\ReflectionAttribute::class);

        $mock->method('getName')
            ->willReturn(ElasticEntity::class);

        $mock->method('newInstance')
            ->willReturn((object) [
                'index' => $index,
                'shards' => $shards,
                'replicas' => $replicas,
                'refreshInterval' => $refreshInterval,
                'settings' => $settings,
            ]);

        return $mock;
    }

    public function testAddAndRetrieveMetadata(): void
    {
        $cache = new ArrayAdapter();
        $collector = new MetadataCollector($cache, $this->createMock(TranslatorInterface::class));

        $collector->addMetadata(\stdClass::class);

        $metadata = $collector->getMetadata(\stdClass::class);

        $this->assertNotNull($metadata);
        $this->assertEquals(\stdClass::class, $metadata['class']);
        $this->assertCount(1, $metadata['attributes']); // Ajusté pour vérifier la présence de l'attribut
        $this->assertInstanceOf(\ReflectionAttribute::class, $metadata['attributes'][0]);
        $this->assertEquals('AllowDynamicProperties', $metadata['attributes'][0]->getName());
    }

    public function testDynamicMetadataLoading(): void
    {
        $cache = new ArrayAdapter();
        $collector = new MetadataCollector($cache, $this->createMock(TranslatorInterface::class));

        $metadata = $collector->getMetadata(\stdClass::class);

        $this->assertNotNull($metadata);
        $this->assertEquals(\stdClass::class, $metadata['class']);
        $this->assertCount(1, $metadata['attributes']); // Ajusté pour vérifier la présence de l'attribut
        $this->assertInstanceOf(\ReflectionAttribute::class, $metadata['attributes'][0]);
        $this->assertEquals('AllowDynamicProperties', $metadata['attributes'][0]->getName());
    }

    public function testClearMetadata(): void
    {
        $cache = new ArrayAdapter();
        $collector = new MetadataCollector($cache, $this->createMock(TranslatorInterface::class));

        $collector->addMetadata(\stdClass::class);

        $collector->clearMetadata();

        // Le cache a été vidé, les métadonnées doivent être recalculées à partir de zéro
        $metadata = $collector->getMetadata(\stdClass::class);
        $this->assertNotNull($metadata);
        $this->assertEquals(\stdClass::class, $metadata['class']);
        $this->assertCount(1, $metadata['attributes']);
    }

    public function testExceptionForNonExistentClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Class NonExistentClass does not exist.');

        $cache = new ArrayAdapter();
        $collector = new MetadataCollector($cache, $this->createMock(TranslatorInterface::class));

        $collector->getMetadata('NonExistentClass');
    }
}
