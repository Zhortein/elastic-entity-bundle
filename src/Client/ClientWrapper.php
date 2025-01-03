<?php

namespace Zhortein\ElasticEntityBundle\Client;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\AuthenticationException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Zhortein\ElasticEntityBundle\DependencyInjection\Configuration;
use Zhortein\ElasticEntityBundle\Metrics\QueryMetrics;

class ClientWrapper
{
    private Client $client;
    private ?QueryMetrics $lastQueryMetrics = null;

    /**
     * @param string[] $hosts
     *
     * @throws AuthenticationException
     */
    public function __construct(
        protected array $hosts = Configuration::DEFAULT_HOSTS,
        protected int $retries = Configuration::DEFAULT_RETRIES,
        protected string $caBundlePath = Configuration::DEFAULT_CA_BUNDLE_PATH,
        protected readonly bool $elasticMetaHeaders = Configuration::DEFAULT_ELASTIC_META_HEADER,
    ) {
        $clientBuilder = ClientBuilder::create()
            ->setHosts($this->hosts);

        if (!empty($this->caBundlePath)) {
            $clientBuilder->setCABundle($this->caBundlePath);
        }

        if (!empty($this->retries)) {
            $clientBuilder->setRetries($this->retries);
        }

        $clientBuilder->setElasticMetaHeader($this->elasticMetaHeaders);

        $this->client = $clientBuilder->build();
    }

    public function getHosts(): array
    {
        return $this->hosts;
    }

    public function getLastQueryMetrics(): ?QueryMetrics
    {
        return $this->lastQueryMetrics;
    }

    /**
     * Perform a GET request.
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function get(array $params): array
    {
        return $this->execute('get', $params);
    }

    /**
     * Perform a search request.
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function search(array $params): array
    {
        return $this->execute('search', $params);
    }

    /**
     * Perform a bulk operation.
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function bulk(array $params): array
    {
        return $this->execute('bulk', $params);
    }

    /**
     * Update a document in Elasticsearch.
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed> The Elasticsearch response
     */
    public function update(array $params): array
    {
        return $this->execute('update', $params);
    }

    /**
     *  Perform an operation.
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function execute(string $operation, array $params): array
    {
        try {
            $start = microtime(true);

            $response = $this->client->$operation($params); // Dynamic call

            /** @var array<string, mixed> $returnResponse */
            $returnResponse = $response instanceof Elasticsearch ? $response->asArray() : (array) $response;

            $executionTime = microtime(true) - $start;
            // Validation des résultats pour éviter les erreurs PHPStan
            $hits = $returnResponse['hits'] ?? null;
            $totalResults = 0;

            if (is_array($hits) && isset($hits['total']) && is_array($hits['total']) && isset($hits['total']['value']) && is_numeric($hits['total']['value'])) {
                $totalResults = (int) $hits['total']['value'];
            }

            // Store metrics
            $this->lastQueryMetrics = new QueryMetrics($executionTime, $totalResults);

            return $returnResponse;
        } catch (ClientResponseException|ServerResponseException|ElasticsearchException $e) {
            throw new \RuntimeException(sprintf('Error during %s request: %s', strtoupper($operation), $e->getMessage()), $e->getCode(), $e);
        }
    }

    /**
     * Count in Elasticsearch.
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed> The Elasticsearch response
     */
    public function count(array $params): array
    {
        return $this->execute('count', $params);
    }
}
