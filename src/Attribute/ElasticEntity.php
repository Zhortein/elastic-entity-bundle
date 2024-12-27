<?php

namespace Zhortein\ElasticEntityBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ElasticEntity
{
    /**
     * @param string               $index           name of the Elasticsearch index
     * @param int|null             $shards          number of shards for the index
     * @param int|null             $replicas        number of replicas for the index
     * @param string|null          $refreshInterval refresh interval for the index
     * @param array<string, mixed> $settings        advanced settings for the index
     */
    public function __construct(
        public string $index,
        public ?int $shards = null,
        public ?int $replicas = null,
        public ?string $refreshInterval = null,
        public array $settings = [],
    ) {
        $this->validateIndex($index);
        $this->validateShards($shards);
        $this->validateRefreshInterval($refreshInterval);
    }

    private function validateIndex(string $index): void
    {
        if (!preg_match('/^[a-z0-9_\-]+$/', $index)) {
            throw new \InvalidArgumentException("Invalid index name: '{$index}'. Index names must match the pattern [a-z0-9_\-].");
        }
    }

    private function validateShards(?int $shards): void
    {
        if (null !== $shards && $shards <= 0) {
            throw new \InvalidArgumentException("Shards must be greater than 0 if specified. Given: {$shards}.");
        }
    }

    private function validateRefreshInterval(?string $refreshInterval): void
    {
        if (null !== $refreshInterval && !preg_match('/^\d+([smh])$/', $refreshInterval)) {
            throw new \InvalidArgumentException("Invalid refresh interval: '{$refreshInterval}'. Examples of valid formats: '1s', '5m', '1h'.");
        }
    }
}
