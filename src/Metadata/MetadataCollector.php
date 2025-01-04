<?php

namespace Zhortein\ElasticEntityBundle\Metadata;

use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zhortein\ElasticEntityBundle\Attribute\ElasticEntity;

class MetadataCollector
{
    private const int CACHE_LIFETIME = 3600;

    /**
     * @var array<string, array{
     *     class: class-string,
     *     index: array{
     *           index: string,
     *           shards: int|null,
     *           replicas: int|null,
     *           refreshInterval: string|null,
     *           settings: array<string, mixed>
     *       }
     * }|null>
     */
    private array $metadata = [];

    private CacheInterface $cache;
    private TranslatorInterface $translator;

    public function __construct(CacheInterface $cache, TranslatorInterface $translator)
    {
        $this->cache = $cache;
        $this->translator = $translator;
    }

    /**
     * @return array{
     *     class: class-string,
     *     index: array{
     *         index: string,
     *         shards: int|null,
     *         replicas: int|null,
     *         refreshInterval: string|null,
     *         settings: array<string, mixed>
     *     }
     * }
     */
    public function loadMetadata(string $className): array
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException("Class $className does not exist.");
        }

        $reflectionClass = new \ReflectionClass($className);
        $indexConfig = [
            'index' => '',
            'shards' => null,
            'replicas' => null,
            'refreshInterval' => null,
            'settings' => [],
        ];
        foreach ($reflectionClass->getAttributes() as $attribute) {
            if ($attribute instanceof \ReflectionAttribute && ElasticEntity::class === $attribute->getName()) {
                /** @var ElasticEntity $instance */
                $instance = $attribute->newInstance();
                $indexConfig = [
                    'index' => $instance->index,
                    'shards' => $instance->shards,
                    'replicas' => $instance->replicas,
                    'refreshInterval' => $instance->refreshInterval,
                    'settings' => $instance->settings,
                ];
                break;
            }
        }

        return [
            'class' => $className,
            'index' => $indexConfig,
        ];
    }

    /**
     * Retrieve metadata for a specific ElasticEntity class.
     *
     * @param class-string $className
     *
     * @return array{
     *      class: class-string,
     *      index: array{
     *          index: string,
     *          shards: int|null,
     *          replicas: int|null,
     *          refreshInterval: string|null,
     *          settings: array<string, mixed>
     *      }
     *  }|null
     *
     * @throws InvalidArgumentException
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
     *      index: array{
     *           index: string,
     *           shards: int|null,
     *           replicas: int|null,
     *           refreshInterval: string|null,
     *           settings: array<string, mixed>
     *       }
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
            throw new \RuntimeException($this->translator->trans('metadata.failed-to-clear', ['exceptionMessage' => $e->getMessage()], 'zhortein_elastic_entity-metadata'), $e->getCode(), $e);
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
