<?php

namespace Zhortein\ElasticEntityBundle\Metadata;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class MetadataCollector
{
    /**
     * @var array<string, array{
     *     class: string,
     *     attributes: \ReflectionAttribute<object>[]
     * }|null>
     */
    private array $metadata = [];

    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Add metadata for an ElasticEntity class.
     *
     * @param \ReflectionClass<object> $reflectionClass
     */
    public function addMetadata(\ReflectionClass $reflectionClass): void
    {
        $className = $reflectionClass->getName();

        $this->metadata[$className] = [
            'class' => $className,
            'attributes' => $reflectionClass->getAttributes(),
        ];

        // Save metadata to cache
        $this->cache->delete($this->getCacheKey($className));
        $this->cache->get($this->getCacheKey($className), function (ItemInterface $item) use ($className) {
            $item->set($this->metadata[$className]);

            return $this->metadata[$className];
        });
    }

    /**
     * Retrieve metadata for a specific ElasticEntity class.
     *
     * @return array{
     *      class: string,
     *      attributes: \ReflectionAttribute<object>[]
     *  }|null
     */
    public function getMetadata(string $className): ?array
    {
        return $this->cache->get($this->getCacheKey($className), function (ItemInterface $item) use ($className) {
            return $this->metadata[$className] ?? null;
        });
    }

    /**
     * Retrieve all metadata.
     *
     * @return array<string, array{
     *      class: string,
     *      attributes: \ReflectionAttribute<object>[]
     *  }|null>
     */
    public function getAllMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Clear all stored metadata.
     */
    public function clearMetadata(): void
    {
        $this->metadata = [];
        if (method_exists($this->cache, 'clear')) {
            $this->cache->clear();
        }
    }

    /**
     * Generate a cache key for a class name.
     */
    private function getCacheKey(string $className): string
    {
        return 'elastic_entity_metadata_'.md5($className);
    }
}
