<?php

namespace Zhortein\ElasticEntityBundle\Tests\Manager;

use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zhortein\ElasticEntityBundle\Attribute\ElasticEntity;
use Zhortein\ElasticEntityBundle\Client\ClientWrapper;
use Zhortein\ElasticEntityBundle\Manager\ElasticEntityManager;
use Zhortein\ElasticEntityBundle\Metadata\MetadataCollector;
use Zhortein\ElasticEntityBundle\Tests\Fixtures\Customer;
use Zhortein\ElasticEntityBundle\Tests\Fixtures\DummyEntity;
use Zhortein\ElasticEntityBundle\Tests\Fixtures\DummyEntityWithoutFields;
use Zhortein\ElasticEntityBundle\Tests\Fixtures\Order;
use Zhortein\ElasticEntityBundle\Tests\Fixtures\Product;

class ElasticEntityManagerPersistRemoveFlushTest extends TestCase
{
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

    private function configureMetadataCollectorMock($metadataCollectorMock): void
    {
        $metadataCollectorMock
            ->method('getMetadata')
            ->with(DummyEntity::class)
            ->willReturn([
                'class' => DummyEntity::class,
                'attributes' => [$this->createElasticEntityAttributeMock('dummy_index')],
            ]);
    }

    public function testPersist(): void
    {
        $clientMock = $this->createMock(ClientWrapper::class);
        $metadataCollectorMock = $this->createMock(MetadataCollector::class);
        $eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $this->configureMetadataCollectorMock($metadataCollectorMock);

        $entityManager = new ElasticEntityManager($clientMock, $metadataCollectorMock, $eventDispatcherMock, $validatorMock,
            $this->createMock(TranslatorInterface::class));

        $entity = new DummyEntity('123', 'Test Entity');
        $entityManager->persist($entity);

        $reflection = new \ReflectionClass($entityManager);
        $pendingOperations = $reflection->getProperty('pendingOperations');

        $operations = $pendingOperations->getValue($entityManager);
        $this->assertCount(1, $operations);
        $this->assertArrayHasKey('index', $operations[0]);
        $this->assertEquals('123', $operations[0]['index']['_id']);
    }

    public function testRemove(): void
    {
        $clientMock = $this->createMock(ClientWrapper::class);
        $metadataCollectorMock = $this->createMock(MetadataCollector::class);
        $eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $this->configureMetadataCollectorMock($metadataCollectorMock);

        $entityManager = new ElasticEntityManager($clientMock, $metadataCollectorMock, $eventDispatcherMock, $validatorMock,
            $this->createMock(TranslatorInterface::class));

        $entity = new DummyEntity('123', 'Test Entity');
        $entityManager->remove($entity);

        $reflection = new \ReflectionClass($entityManager);
        $pendingOperations = $reflection->getProperty('pendingOperations');

        $operations = $pendingOperations->getValue($entityManager);
        $this->assertCount(1, $operations);
        $this->assertArrayHasKey('delete', $operations[0]);
        $this->assertEquals('123', $operations[0]['delete']['_id']);
    }

    public function testFlush(): void
    {
        $clientMock = $this->createMock(ClientWrapper::class);
        $clientMock
            ->method('bulk')
            ->with($this->callback(function ($params) {
                $this->assertArrayHasKey('body', $params);
                $this->assertCount(4, $params['body']);

                return true;
            }));
        $metadataCollectorMock = $this->createMock(MetadataCollector::class);
        $eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $this->configureMetadataCollectorMock($metadataCollectorMock);

        $entityManager = new ElasticEntityManager($clientMock, $metadataCollectorMock, $eventDispatcherMock, $validatorMock,
            $this->createMock(TranslatorInterface::class));

        $entity = new DummyEntity('123', 'Test Entity');
        $entityManager->persist($entity);
        $entityManager->flush();

        $reflection = new \ReflectionClass($entityManager);
        $pendingOperations = $reflection->getProperty('pendingOperations');

        $this->assertEmpty($pendingOperations->getValue($entityManager));
    }

    public function testPersistIgnoresFieldsWithoutElasticFieldAnnotation(): void
    {
        $clientMock = $this->createMock(ClientWrapper::class);
        $metadataCollectorMock = $this->createMock(MetadataCollector::class);
        $eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $validatorMock = $this->createMock(ValidatorInterface::class);

        $metadataCollectorMock->expects($this->once())
            ->method('getMetadata')
            ->with(DummyEntityWithoutFields::class)
            ->willReturn([
                'class' => DummyEntityWithoutFields::class,
                'attributes' => [
                    $this->createElasticEntityAttributeMock('dummy_index'),
                ],
            ]);

        $manager = new ElasticEntityManager($clientMock, $metadataCollectorMock, $eventDispatcherMock, $validatorMock,
            $this->createMock(TranslatorInterface::class));

        $entity = (new DummyEntityWithoutFields())->setId('123');

        $manager->persist($entity);

        $reflection = new \ReflectionClass($manager);
        $pendingOperations = $reflection->getProperty('pendingOperations');

        $operations = $pendingOperations->getValue($manager);
        $this->assertEmpty($operations[0]['data']);
    }

    public function testPersistWithNestedRelation(): void
    {
        $clientMock = $this->createMock(ClientWrapper::class);
        $metadataCollectorMock = $this->createMock(MetadataCollector::class);
        $eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $validatorMock = $this->createMock(ValidatorInterface::class);

        $metadataCollectorMock->expects($this->once())
            ->method('getMetadata')
            ->with(Order::class)
            ->willReturn([
                'class' => Order::class,
                'attributes' => [
                    $this->createElasticEntityAttributeMock('orders'),
                ],
            ]);

        $manager = new ElasticEntityManager($clientMock, $metadataCollectorMock, $eventDispatcherMock, $validatorMock,
            $this->createMock(TranslatorInterface::class));

        $product1 = new Product();
        $product1->setName('Product 1');

        $product2 = new Product();
        $product2->setName('Product 2');

        $order = new Order();
        $order->setOrderNumber('ORD-12345');
        $order->setProducts([$product1, $product2]);

        $manager->persist($order);

        $reflection = new \ReflectionClass($manager);
        $pendingOperations = $reflection->getProperty('pendingOperations');

        $operations = $pendingOperations->getValue($manager);

        $this->assertNotEmpty($operations[0]['data']['products']);
        $this->assertCount(2, $operations[0]['data']['products']);
        $this->assertEquals($product1->getId(), $operations[0]['data']['products'][0]);
    }

    public function testPersistWithReferenceRelation(): void
    {
        $clientMock = $this->createMock(ClientWrapper::class);
        $metadataCollectorMock = $this->createMock(MetadataCollector::class);
        $eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $validatorMock = $this->createMock(ValidatorInterface::class);

        $metadataCollectorMock->expects($this->once())
            ->method('getMetadata')
            ->with(Order::class)
            ->willReturn([
                'class' => Order::class,
                'attributes' => [
                    $this->createElasticEntityAttributeMock('orders'),
                ],
            ]);

        $manager = new ElasticEntityManager($clientMock, $metadataCollectorMock, $eventDispatcherMock, $validatorMock,
            $this->createMock(TranslatorInterface::class));

        $customer = new Customer();
        $customer->setId('CUST-123');

        $order = new Order();
        $order->setOrderNumber('ORD-54321');
        $order->setCustomer($customer);

        $manager->persist($order);

        $reflection = new \ReflectionClass($manager);
        $pendingOperations = $reflection->getProperty('pendingOperations');

        $operations = $pendingOperations->getValue($manager);

        $this->assertEquals('CUST-123', $operations[0]['data']['customer']);
    }
}
