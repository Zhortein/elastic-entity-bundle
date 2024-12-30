<?php

namespace Zhortein\ElasticEntityBundle\Tests\Manager;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zhortein\ElasticEntityBundle\Attribute\ElasticEntity;
use Zhortein\ElasticEntityBundle\Client\ClientWrapper;
use Zhortein\ElasticEntityBundle\Manager\ElasticEntityManager;
use Zhortein\ElasticEntityBundle\Metadata\MetadataCollector;
use Zhortein\ElasticEntityBundle\Tests\Fixtures\DummyEntity;

class ElasticEntityManagerCustomQueryTest extends TestCase
{
    private ElasticEntityManager $entityManager;

    private function createElasticEntityAttributeMock(string $index, ?int $shards = null, ?int $replicas = null, ?string $refreshInterval = null, array $settings = []): \ReflectionAttribute
    {
        $reflectionAttributeMock = $this->createMock(\ReflectionAttribute::class);
        $reflectionAttributeMock
            ->method('getName')
            ->willReturn(ElasticEntity::class);

        $reflectionAttributeMock
            ->method('newInstance')
            ->willReturn((object) [
                'index' => $index,
                'shards' => $shards ?? 1, // Valeur par défaut
                'replicas' => $replicas ?? 1, // Valeur par défaut
                'refreshInterval' => $refreshInterval ?? '1s', // Valeur par défaut
                'settings' => $settings,
            ]);

        return $reflectionAttributeMock;
    }

    protected function setUp(): void
    {
        $clientMock = $this->createMock(ClientWrapper::class);
        $clientMock->method('search')->willReturn([
            'hits' => [
                'hits' => [
                    ['_source' => ['field' => 'value']],
                ],
            ],
        ]);
        $clientMock->method('count')->willReturn(['count' => 42]);

        $metadataCollectorMock = $this->createMock(MetadataCollector::class);
        $metadataCollectorMock->method('getMetadata')
            ->with(DummyEntity::class)
            ->willReturn([
                'class' => DummyEntity::class,
                'attributes' => [$this->createElasticEntityAttributeMock('dummy_index')],
            ]);

        $eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);

        $this->entityManager = new ElasticEntityManager(
            $clientMock,
            $metadataCollectorMock,
            $eventDispatcherMock,
            $this->createMock(ValidatorInterface::class),
            $this->createMock(TranslatorInterface::class)
        );
    }

    public function testExecuteCustomQueryReturnsRawResults(): void
    {
        $results = $this->entityManager->executeCustomQuery(null, ['query' => ['match_all' => []]], ['index' => 'test_index']);

        $this->assertIsArray($results);
        $this->assertSame('value', $results[0]['_source']['field']);
    }

    public function testExecuteCustomQueryHydratesEntities(): void
    {
        $results = $this->entityManager->executeCustomQuery(DummyEntity::class, ['query' => ['match_all' => []]]);

        $this->assertIsArray($results);
        $this->assertInstanceOf(DummyEntity::class, $results[0]);
    }

    public function testCountCustomQuery(): void
    {
        $count = $this->entityManager->countCustomQuery('test_index', ['query' => ['match_all' => []]]);

        $this->assertSame(42, $count);
    }
}
