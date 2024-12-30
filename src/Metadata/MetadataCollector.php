<?php

namespace Zhortein\ElasticEntityBundle\Metadata;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class MetadataCollector
{
    private const CACHE_LIFETIME = 3600;

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
     * @return array{
     *     class: string,
     *     attributes: \ReflectionAttribute<object>[]
     * }
     */
    private function loadMetadata(string $className): array
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException("Class $className does not exist.");
        }

        $reflectionClass = new \ReflectionClass($className);

        return [
            'class' => $className,
            'attributes' => $reflectionClass->getAttributes(),
        ];
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
            $item->expiresAfter(self::CACHE_LIFETIME);
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
            $item->expiresAfter(self::CACHE_LIFETIME);

            if (!isset($this->metadata[$className])) {
                $this->metadata[$className] = $this->loadMetadata($className);
            }

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
        try {
            if (method_exists($this->cache, 'clear')) {
                $this->cache->clear(); // Supprime tous les caches
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to clear metadata cache: '.$e->getMessage(), $e->getCode(), $e);
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
