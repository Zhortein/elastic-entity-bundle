<?php

namespace Zhortein\ElasticEntityBundle\DTO;

class ElasticEntityMetadataDTO
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
    }

    /**
     * @param array{
     *     index: string,
     *     shards: int|null,
     *     replicas: int|null,
     *     refreshInterval: string|null,
     *     settings: array<string, mixed>
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            index: isset($data['index']) && is_string($data['index']) ? $data['index'] : '',
            shards: isset($data['shards']) && is_int($data['shards']) ? $data['shards'] : null,
            replicas: isset($data['replicas']) && is_int($data['replicas']) ? $data['replicas'] : null,
            refreshInterval: isset($data['refresh_interval']) && is_string($data['refresh_interval']) ? $data['refresh_interval'] : null,
            settings: isset($data['settings']) && is_array($data['settings']) ? $data['settings'] : [],
        );
    }

    /**
     * @return array{
     *      index: string,
     *      shards: int|null,
     *      replicas: int|null,
     *      refreshInterval: string|null,
     *      settings: array<string, mixed>
     * }
     */
    public function toArray(): array
    {
        return [
            'index' => $this->index,
            'shards' => $this->shards,
            'replicas' => $this->replicas,
            'refreshInterval' => $this->refreshInterval,
            'settings' => $this->settings,
        ];
    }
}
