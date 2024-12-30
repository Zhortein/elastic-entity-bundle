<?php

namespace Zhortein\ElasticEntityBundle\Manager;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zhortein\ElasticEntityBundle\Attribute\ElasticEntity;
use Zhortein\ElasticEntityBundle\Attribute\ElasticField;
use Zhortein\ElasticEntityBundle\Attribute\ElasticRelation;
use Zhortein\ElasticEntityBundle\Client\ClientWrapper;
use Zhortein\ElasticEntityBundle\Contracts\ElasticEntityInterface;
use Zhortein\ElasticEntityBundle\Event\ElasticEntityEvent;
use Zhortein\ElasticEntityBundle\Exception\ValidationException;
use Zhortein\ElasticEntityBundle\Metadata\MetadataCollector;
use Zhortein\ElasticEntityBundle\Metrics\QueryMetrics;

class ElasticEntityManager
{
    /**
     * @var array<int, array{
     *     entity: ElasticEntityInterface,
     *     index?: array{
     *         _index: mixed,
     *         _id: mixed
     *     },
     *     delete?: array{
     *          _index: mixed,
     *          _id: mixed
     *     },
     *     update?: array{
     *          _index: mixed,
     *          _id: mixed
     *     },
     *     data?: array<int|string, mixed>
     * }> pending operations for bulk actions
     */
    private array $pendingOperations = [];

    /**
     * @var array<string, array<string, mixed>> Map of object hashes to snapshots for change detection
     */
    private array $snapshots = [];

    /**
     * @var array<string, ElasticEntityInterface> Map of object hashes to entities for tracking
     */
    private array $trackedEntities = [];

    public function __construct(
        private readonly ClientWrapper $client,
        private readonly MetadataCollector $metadataCollector,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ValidatorInterface $validator,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getClient(): ClientWrapper
    {
        return $this->client;
    }

    /**
     * @throws \ReflectionException
     */
    private function validate(object $entity): void
    {
        $violations = $this->validator->validate($entity);

        if ($violations->count() > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage());
            }

            throw new ValidationException($this->translator, implode(', ', $messages));
        }

        // Validation des relations
        $className = $entity::class;
        $fieldConfigs = $this->getFieldConfigurations($className);

        foreach ($fieldConfigs as $field => $config) {
            if (isset($config['relation']) && is_array($config['relation']) && isset($config['relation']['targetClass'])) {
                $relatedClass = $config['relation']['targetClass'];
                $property = new \ReflectionProperty($entity, $field);

                if ($property->isInitialized($entity)) {
                    $relatedValue = $property->getValue($entity);
                    if (is_array($relatedValue)) {
                        foreach ($relatedValue as $relatedEntity) {
                            if (is_object($relatedEntity)) {
                                $this->validate($relatedEntity);
                            }
                        }
                    } elseif (is_object($relatedValue)) {
                        $this->validate($relatedValue);
                    }
                }
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function captureSnapshot(object $entity): array
    {
        $snapshot = [];
        $reflectionClass = new \ReflectionClass($entity);
        foreach ($reflectionClass->getProperties() as $property) {
            if (in_array($property->getName(), ['persisted', 'modified', 'pendingOperation'])) {
                continue;
            }
            $snapshot[$property->getName()] = $property->getValue($entity);
        }

        return $snapshot;
    }

    /**
     * @param array<string, mixed> $snapshot
     *
     * @return array<string, mixed>
     */
    private function detectChanges(object $entity, array $snapshot): array
    {
        $changes = [];
        $reflectionClass = new \ReflectionClass($entity);
        foreach ($reflectionClass->getProperties() as $property) {
            if (in_array($property->getName(), ['persisted', 'modified', 'pendingOperation'])) {
                continue;
            }
            $currentValue = $property->getValue($entity);
            if ($snapshot[$property->getName()] !== $currentValue) {
                $changes[$property->getName()] = $currentValue;
            }
        }

        return $changes;
    }

    /**
     * Retrieve the index name and configuration for a given class.
     *
     * @param string $className fully qualified class name of the entity
     *
     * @return array<string, mixed> configuration of the Elasticsearch index
     */
    private function getIndexConfiguration(string $className): array
    {
        $metadata = $this->metadataCollector->getMetadata($className);

        if (null === $metadata || !array_key_exists('attributes', $metadata)) {
            throw new \RuntimeException($this->translator->trans('manager.no-metatadata-for-class', ['className' => $className], 'zhortein_elastic_entity-manager'));
        }

        foreach ($metadata['attributes'] as $attribute) {
            if ($attribute instanceof \ReflectionAttribute && ElasticEntity::class === $attribute->getName()) {
                /** @var ElasticEntity $instance */
                $instance = $attribute->newInstance();

                return [
                    'index' => $instance->index,
                    'shards' => $instance->shards,
                    'replicas' => $instance->replicas,
                    'refreshInterval' => $instance->refreshInterval,
                    'settings' => $instance->settings,
                ];
            }
        }

        throw new \RuntimeException($this->translator->trans('manager.no-elastic-entity-attribute-for-class', ['className' => $className], 'zhortein_elastic_entity-manager'));
    }

    /**
     * Retrieve the field configuration for a given entity.
     *
     * @param class-string $className fully qualified class name of the entity
     *
     * @return array<string, array<string, mixed>> configuration of each field in the entity
     *
     * @throws \ReflectionException
     */
    private function getFieldConfigurations(string $className): array
    {
        $fields = [];
        $reflectionClass = new \ReflectionClass($className);
        foreach ($reflectionClass->getProperties() as $property) {
            $fieldConfig = [];

            // Gérer l'attribut ElasticField
            foreach ($property->getAttributes(ElasticField::class) as $attribute) {
                /** @var ElasticField $instance */
                $instance = $attribute->newInstance();
                $fieldConfig = [
                    'type' => $instance->type,
                    'nullable' => $instance->nullable,
                    'analyzer' => $instance->analyzer,
                    'directives' => $instance->directives,
                ];
            }

            // Gérer l'attribut ElasticRelation
            foreach ($property->getAttributes(ElasticRelation::class) as $attribute) {
                /** @var ElasticRelation $instance */
                $instance = $attribute->newInstance();
                $fieldConfig['relation'] = [
                    'targetClass' => $instance->targetClass,
                    'type' => $instance->type,
                ];
            }

            if (!empty($fieldConfig)) {
                $fields[$property->getName()] = $fieldConfig;
            }
        }

        return $fields;
    }

    /**
     * Extracts the ID or full data of a related entity.
     *
     * @param object       $entity    the related entity
     * @param class-string $className the fully qualified class name of the related entity
     *
     * @return array<string, mixed>|string the ID of the entity or its full data
     *
     * @throws \InvalidArgumentException if the entity does not have an ID and cannot be mapped
     * @throws \ReflectionException
     */
    private function extractIdOrData(object $entity, string $className): array|string
    {
        if (method_exists($entity, 'getId')) {
            /** @var string $id */
            $id = $entity->getId();

            return $id;
        }

        // Return the full field configuration if no ID is available
        $fieldConfigurations = $this->getFieldConfigurations($className);

        if (empty($fieldConfigurations)) {
            throw new \InvalidArgumentException($this->translator->trans('manager.invalid-identifier-for-class', ['className' => $className], 'zhortein_elastic_entity-manager'));
        }

        return $fieldConfigurations;
    }

    /**
     * Retrieves the metrics for the last "Query".
     */
    public function getLastqueryMetrics(): ?QueryMetrics
    {
        return $this->client->getLastQueryMetrics();
    }

    /**
     * @param class-string $relatedClass
     *
     * @return array<string, mixed>|string the ID of the entity or its full data
     *
     * @throws \ReflectionException
     */
    private function processRelatedEntity(object $related, string $relatedClass): string|array
    {
        if (!$related instanceof ElasticEntityInterface) {
            throw new \InvalidArgumentException($this->translator->trans('manager.entity-must-implement-interface', ['className' => $relatedClass], 'zhortein_elastic_entity-manager'));
        }

        // Valider l'entité liée avant de l'inclure dans les données
        $this->validate($related);

        return $this->extractIdOrData($related, $relatedClass);
    }

    /**
     * Persist an entity (create or update).
     *
     * @param object $entity the entity to persist
     *
     * @throws \InvalidArgumentException if the entity does not have a valid ID
     * @throws \ReflectionException
     */
    public function persist(object $entity): void
    {
        if (!$entity instanceof ElasticEntityInterface) {
            throw new \InvalidArgumentException($this->translator->trans('manager.entity-must-implement-interface', ['className' => $entity::class], 'zhortein_elastic_entity-manager'));
        }

        $this->validate($entity);

        $className = $entity::class;
        $indexConfig = $this->getIndexConfiguration($className);
        $index = $indexConfig['index'];

        $id = $entity->getId();
        if (!$id) {
            throw new \InvalidArgumentException($this->translator->trans('manager.entity-must-implement-valid-getid', ['className' => $className], 'zhortein_elastic_entity-manager'));
        }

        // Vérifie si l'opération existe déjà
        foreach ($this->pendingOperations as $operation) {
            if (array_key_exists('index', $operation) && $operation['index']['_index'] === $index && $operation['index']['_id'] === $id) {
                return; // Ignore les doublons
            }
        }

        $hash = spl_object_hash($entity);
        $this->trackedEntities[$hash] = $entity;
        $this->snapshots[$hash] = $this->captureSnapshot($entity);

        $data = $this->prepareDataForPersistence($className, $entity);

        $this->pendingOperations[] = [
            'entity' => $entity,
            'index' => [
                '_index' => $index,
                '_id' => $id,
            ],
            'data' => $data,
        ];

        $entity->setEntityHavePendingOperation(true);
    }

    /**
     * Remove an entity by ID.
     *
     * @param object $entity the entity to remove
     *
     * @throws \InvalidArgumentException if the entity does not have a valid ID
     */
    public function remove(object $entity): void
    {
        if (!$entity instanceof ElasticEntityInterface) {
            throw new \InvalidArgumentException($this->translator->trans('manager.entity-must-implement-interface', ['className' => $entity::class], 'zhortein_elastic_entity-manager'));
        }

        $className = $entity::class;
        $indexConfig = $this->getIndexConfiguration($className);
        $index = $indexConfig['index'];

        $id = $entity->getId();
        if (!$id) {
            throw new \InvalidArgumentException($this->translator->trans('manager.entity-must-implement-valid-getid', ['className' => $className], 'zhortein_elastic_entity-manager'));
        }

        $this->pendingOperations[] = [
            'entity' => $entity,
            'delete' => [
                '_index' => $index,
                '_id' => $id,
            ],
        ];

        $entity->setEntityHavePendingOperation(true);
    }

    /**
     * Flush pending operations to Elasticsearch.
     *
     * Clears the pending operations after execution.
     */
    public function flush(): void
    {
        if (empty($this->pendingOperations) && empty($this->trackedEntities)) {
            // Nothing to be flushed or changes to detect for flushing
            return;
        }

        foreach ($this->trackedEntities as $hash => $entity) {
            $changes = $this->detectChanges($entity, $this->snapshots[$hash]);
            if (!empty($changes)) {
                // Add update operation to pending operations
                $className = $entity::class;
                $indexConfig = $this->getIndexConfiguration($className);
                $index = $indexConfig['index'];
                $this->pendingOperations[] = [
                    'entity' => $entity,
                    'update' => [
                        '_index' => $index,
                        '_id' => $entity->getId(),
                    ],
                    'data' => ['doc' => $changes],
                ];
                $entity->setEntityModified(true);
            }

            // Refresh snapshot after processing changes
            $this->snapshots[$hash] = $this->captureSnapshot($entity);
        }

        if (empty($this->pendingOperations)) {
            // Nothing to flush
            return;
        }

        $bulkPayload = [];
        foreach ($this->pendingOperations as $rank => $operation) {
            if (!$operation['entity'] instanceof ElasticEntityInterface) {
                // Ignore operations on object not implementing ElasticEntityInterface
                continue;
            }

            if (isset($operation['index'], $operation['data'])) {
                $this->eventDispatcher->dispatch(new ElasticEntityEvent($operation['entity']), 'elastic_entity.pre_persist');
                $bulkPayload[] = ['index' => $operation['index']];
                $bulkPayload[] = $operation['data'];
            } elseif (isset($operation['delete'])) {
                $this->eventDispatcher->dispatch(new ElasticEntityEvent($operation['entity']), 'elastic_entity.pre_remove');
                $bulkPayload[] = ['delete' => $operation['delete']];
            } elseif (isset($operation['update'], $operation['data'])) {
                $this->eventDispatcher->dispatch(new ElasticEntityEvent($operation['entity']), 'elastic_entity.pre_update');
                $bulkPayload[] = ['update' => $operation['update']];
                $bulkPayload[] = ['data' => $operation['data']];
            }

            $operation['entity']->setEntityHavePendingOperation(false);
        }

        $this->client->bulk(['body' => $bulkPayload]);

        foreach ($this->pendingOperations as $operation) {
            if (isset($operation['index'])) {
                $operation['entity']->setEntityPersisted(true);
                $this->eventDispatcher->dispatch(new ElasticEntityEvent($operation['entity']), 'elastic_entity.post_persist');
            } elseif (isset($operation['delete'])) {
                $operation['entity']->setEntityPersisted(false);
                $this->eventDispatcher->dispatch(new ElasticEntityEvent($operation['entity']), 'elastic_entity.post_remove');
            } elseif (isset($operation['update'])) {
                $operation['entity']->setEntityPersisted(true);
                $operation['entity']->setEntityModified(false);
                $this->eventDispatcher->dispatch(new ElasticEntityEvent($operation['entity']), 'elastic_entity.post_update');
            }
        }

        $this->pendingOperations = [];
    }

    /**
     * Find an entity by its ID.
     *
     * @param class-string $className fully qualified class name of the entity
     * @param string       $id        the ID of the entity to find
     *
     * @return object|null the entity if found, or null
     */
    public function find(string $className, string $id): ?object
    {
        $indexConfig = $this->getIndexConfiguration($className);
        $index = $indexConfig['index'];

        /** @var array<string, mixed> $response */
        $response = $this->client->get([
            'index' => $index,
            'id' => $id,
        ]);

        if (!$response['found']) {
            return null;
        }

        /** @var array<string, mixed> $source */
        $source = $response['_source'];

        return $this->hydrateEntity($className, $source, true);
    }

    /**
     * Find one entity by criteria.
     *
     * @param class-string          $className fully qualified class name of the entity
     * @param array<string, mixed>  $criteria  search criteria ['field' => 'value1', 'other_field' => ...], accept ElasticSearch expressions array as values
     * @param array<string, string> $orderBy   sort order['field' => 'asc', 'other_field' => 'desc']
     *
     * @return object|null the first matching entity, or null
     */
    public function findOneBy(string $className, array $criteria, array $orderBy = []): ?object
    {
        $results = $this->findBy($className, $criteria, $orderBy, 1);

        return $results[0] ?? null;
    }

    /**
     * Find entities by criteria.
     *
     * @param class-string          $className fully qualified class name of the entity
     * @param array<string, mixed>  $criteria  search criteria ['field' => 'value1', 'other_field' => ...], accept ElasticSearch expressions array as values
     * @param array<string, string> $orderBy   sort order ['field' => 'asc', 'other_field' => 'desc']
     * @param int|null              $limit     maximum number of entities to retrieve
     * @param int|null              $offset    number of entities to skip
     *
     * @return array<object> list of matching entities
     */
    public function findBy(string $className, array $criteria, array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        $indexConfig = $this->getIndexConfiguration($className);
        $index = $indexConfig['index'];
        $fieldConfigs = $this->getFieldConfigurations($className);

        $query = ['bool' => ['must' => []]];
        foreach ($criteria as $field => $condition) {
            if (!isset($fieldConfigs[$field])) {
                throw new \InvalidArgumentException($this->translator->trans('manager.invalid-field-for-entity', ['className' => $className, 'fieldName' => $field], 'zhortein_elastic_entity-manager'));
            }

            // Gestion des critères avancés
            if (is_array($condition) && isset($condition['_range'])) {
                $query['bool']['must'][] = [
                    'range' => [
                        $field => $condition['_range'],
                    ],
                ];
            } elseif (is_array($condition) && isset($condition['_prefix'])) {
                $query['bool']['must'][] = [
                    'prefix' => [
                        $field => $condition['_prefix'],
                    ],
                ];
            } elseif (is_array($condition) && isset($condition['_wildcard'])) {
                $query['bool']['must'][] = [
                    'wildcard' => [
                        $field => $condition['_wildcard'],
                    ],
                ];
            } else {
                $query['bool']['must'][] = ['match' => [$field => $condition]];
            }
        }

        $sort = [];
        foreach ($orderBy as $field => $direction) {
            if (!isset($fieldConfigs[$field])) {
                throw new \InvalidArgumentException($this->translator->trans('manager.invalid-sort-field-for-entity', ['className' => $className, 'fieldName' => $field], 'zhortein_elastic_entity-manager'));
            }
            $sort[] = [$field => ['order' => 'desc' === strtolower($direction) ? 'desc' : 'asc']];
        }

        $params = [
            'index' => $index,
            'body' => [
                'query' => $query,
                'sort' => $sort,
                'from' => $offset,
                'size' => $limit,
            ],
        ];

        /**
         * @var array{
         *     hits: array{
         *         hits: array<int, array{
         *             _source: array<string, mixed>
         *         }>
         *     }
         * } $response
         */
        $response = $this->client->search($params);

        return array_map(function (array $hit) use ($className) {
            return $this->hydrateEntity($className, $hit['_source'], true);
        }, $response['hits']['hits']);
    }

    /**
     * Perform aggregations on an Elasticsearch index.
     *
     * @param class-string         $className    fully qualified class name of the entity
     * @param array<string, mixed> $aggregations the aggregation queries
     * @param array<string, mixed> $criteria     optional search criteria to filter the aggregation
     *
     * @return array<mixed, mixed> aggregation results
     */
    public function aggregate(string $className, array $aggregations, array $criteria = []): array
    {
        $indexConfig = $this->getIndexConfiguration($className);
        $index = $indexConfig['index'];
        $fieldConfigs = $this->getFieldConfigurations($className);

        $query = ['bool' => ['must' => []]];
        foreach ($criteria as $field => $value) {
            if (!isset($fieldConfigs[$field])) {
                throw new \InvalidArgumentException($this->translator->trans('manager.invalid-field-for-entity', ['className' => $className, 'fieldName' => $field], 'zhortein_elastic_entity-manager'));
            }
            $query['bool']['must'][] = ['match' => [$field => $value]];
        }

        $params = [
            'index' => $index,
            'body' => [
                'query' => $query,
                'aggs' => $aggregations,
            ],
        ];

        $response = $this->client->search($params);
        if (!isset($response['aggregations']) || !is_array($response['aggregations'])) {
            throw new \RuntimeException($this->translator->trans('manager.invalid-aggregation-response-format', [], 'zhortein_elastic_entity-manager'));
        }

        return $response['aggregations'];
    }

    /**
     * Hydrate an entity from Elasticsearch source data.
     *
     * @param class-string             $className   fully qualified class name of the entity
     * @param array<int|string, mixed> $data        source data from Elasticsearch
     * @param bool                     $isPersisted HydrateOperation true if hydrated object is persisted in ElasticSearch index
     *
     * @return object the hydrated entity
     */
    private function hydrateEntity(string $className, array $data, bool $isPersisted = false): object
    {
        $entity = new $className();
        if (!$entity instanceof ElasticEntityInterface) {
            throw new \InvalidArgumentException($this->translator->trans('manager.entity-must-implement-interface', ['className' => $className], 'zhortein_elastic_entity-manager'));
        }

        $fieldConfigs = $this->getFieldConfigurations($className);

        foreach ($data as $field => $value) {
            if (!is_string($field) || !isset($fieldConfigs[$field])) {
                continue; // Ignore undefined fields
            }

            $config = $fieldConfigs[$field];

            // Gestion des relations
            if (isset($config['relation'])) {
                /** @var array{
                 *     type: string,
                 *     targetClass: string
                 * } $relation
                 */
                $relation = $config['relation'];
                if ('reference' === $relation['type'] && is_string($value)) {
                    /** @var class-string $relatedClass */
                    $relatedClass = $relation['targetClass'];
                    $value = $this->find($relatedClass, $value); // Charger l'entité liée
                } elseif ('nested' === $relation['type'] && is_array($value)) {
                    /** @var class-string $relatedClass */
                    $relatedClass = $relation['targetClass'];
                    $value = array_map(
                        function ($nestedData) use ($relatedClass, $className) {
                            if (!is_array($nestedData)) {
                                throw new \RuntimeException($this->translator->trans('manager.invalid-nested-data-format', ['className' => $className, 'relatedClassName' => $relatedClass], 'zhortein_elastic_entity-manager'));
                            }

                            if (array_keys($nestedData) !== array_filter(array_keys($nestedData), 'is_string')) {
                                throw new \InvalidArgumentException($this->translator->trans('manager.nested-data-format-error', ['className' => $className, 'relatedClassName' => $relatedClass], 'zhortein_elastic_entity-manager'));
                            }

                            /* @var array<string, mixed> $nestedData */
                            return $this->hydrateEntity($relatedClass, $nestedData);
                        },
                        $value
                    );
                }
            }

            $setter = 'set'.ucfirst($field);
            if (method_exists($entity, $setter)) {
                $entity->$setter($value);
            }
        }

        $entity->setEntityPersisted($isPersisted);

        return $entity;
    }

    /**
     * @param class-string $className
     *
     * @return array<int|string, mixed>
     *
     * @throws \ReflectionException
     */
    private function prepareDataForPersistence(string $className, ElasticEntityInterface $entity): array
    {
        $data = [];
        $fieldConfigs = $this->getFieldConfigurations($className);
        foreach ($fieldConfigs as $field => $config) {
            $property = new \ReflectionProperty($entity, $field);

            if (($config['nullable'] ?? false) === false && !$property->isInitialized($entity)) {
                throw new \InvalidArgumentException($this->translator->trans('manager.field-not-nullable', ['className' => $className, 'fieldName' => $field], 'zhortein_elastic_entity-manager'));
            }

            if ($property->isInitialized($entity)) {
                $value = $property->getValue($entity);

                // Gestion des relations
                if (isset($config['relation']) && is_array($config['relation']) && isset($config['relation']['targetClass'])) {
                    /** @var class-string $relatedClass */
                    $relatedClass = $config['relation']['targetClass'];
                    if (is_array($value)) {
                        $data[$field] = array_filter(array_map(
                            fn ($related) => is_object($related) ? $this->processRelatedEntity($related, $relatedClass) : null,
                            $value
                        ));
                    } elseif (is_object($value)) {
                        $data[$field] = $this->processRelatedEntity($value, $relatedClass);
                    }
                } else {
                    $data[$field] = $value;
                }
            }
        }

        return $data;
    }

    /**
     * Execute a custom query on the given Elasticsearch index.
     *
     * @param class-string|null    $className the entity class name for result hydration (optional)
     * @param array<string, mixed> $query     the custom Elasticsearch query
     * @param array<string, mixed> $options   Additional query options (e.g., sorting, size).
     *
     * @return array<mixed> hydrated entities or raw Elasticsearch results
     */
    public function executeCustomQuery(?string $className, array $query, array $options = []): array
    {
        if ($className) {
            $indexConfig = $this->getIndexConfiguration($className);
            $index = $indexConfig['index'];
        } else {
            $index = $options['index'] ?? throw new \InvalidArgumentException($this->translator->trans('manager.custom-query.index-cannot-be-null', [], 'zhortein_elastic_entity-manager'));
        }

        $params = [
            'index' => $index,
            'body' => $query,
        ];

        if (isset($options['size'])) {
            $params['body']['size'] = $options['size'];
        }
        if (isset($options['from'])) {
            $params['body']['from'] = $options['from'];
        }
        if (isset($options['sort'])) {
            $params['body']['sort'] = $options['sort'];
        }

        $response = $this->client->search($params);

        $results = [];
        if (is_array($response['hits']) && isset($response['hits']['hits']) && is_array($response['hits']['hits'])) {
            $results = array_filter($response['hits']['hits'], static fn ($hit) => is_array($hit) && isset($hit['_source']));
        }

        if ($className) {
            return array_map(function (array $hit) use ($className): object {
                /** @var array<int|string, mixed> $source */
                $source = $hit['_source'];

                return $this->hydrateEntity($className, $source);
            }, $results);
        }

        return $results;
    }

    /**
     * Count entities based on a custom query.
     *
     * @param string               $index the Elasticsearch index to query
     * @param array<string, mixed> $query the custom Elasticsearch query
     *
     * @return int the count of matching entities
     */
    public function countCustomQuery(string $index, array $query): int
    {
        $params = [
            'index' => $index,
            'body' => $query,
        ];

        $response = $this->client->count($params);

        if (!isset($response['count']) || !is_int($response['count'])) {
            throw new \RuntimeException('Unexpected response format from Elasticsearch count query.');
        }

        return $response['count'];
    }

    /**
     * Convert a payload into an ElasticEntityInterface instance.
     *
     * @param class-string<ElasticEntityInterface> $className
     * @param array<string, mixed>                 $payload
     */
    public function hydratePayloadToEntity(string $className, array $payload): ElasticEntityInterface
    {
        $entity = $this->hydrateEntity($className, $payload);
        if (!$entity instanceof ElasticEntityInterface) {
            throw new \InvalidArgumentException($this->translator->trans('manager.entity-must-implement-interface', ['className' => $className], 'zhortein_elastic_entity-manager'));
        }

        return $entity;
    }
}
