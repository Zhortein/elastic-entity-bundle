# Metadata Management

## English Documentation

A [French version](../fr/MetadataManagement.md) is also available.

### Overview

The `MetadataCollector` is responsible for managing metadata related to Elastic Entities. It ensures that metadata is efficiently stored, retrieved, and cached to optimize performance when interacting with Elasticsearch.

---

### Features

- **Add Metadata:** Dynamically add metadata for entities.
- **Retrieve Metadata:** Fetch metadata for a specific class or all entities.
- **Cache Integration:** Use Symfony's caching system to store metadata for improved performance.
- **Clear Metadata:** Clear all metadata from memory and cache when needed.

---

### Methods and Functionalities

#### `addMetadata`

Adds metadata for a given ElasticEntity class.

```php
/**
 * Add metadata for an ElasticEntity class.
 *
 * @param class-string $className
 */
public function addMetadata(string $className): void;
```

- **Parameters:**
    - `$reflectionClass`: A `ReflectionClass` instance representing the entity.
- **Caching:** Saves the metadata to the cache using a unique key.

---

#### `getMetadata`

Retrieves metadata for a specific class.

```php
/**
 * Retrieve metadata for a specific ElasticEntity class.
 *
 * @param string $className
 * @return array<string, mixed>|null
 */
public function getMetadata(string $className): ?array;
```

- **Parameters:**
    - `$className`: The fully qualified class name of the entity.
- **Returns:**
    - The metadata as an associative array, or `null` if no metadata is found.
- **Caching:**
    - Uses the cache to retrieve metadata if available.

---

#### `getAllMetadata`

Fetches metadata for all known classes.

```php
/**
 * Retrieve all metadata.
 *
 * @return array<string, array<string, mixed>|null>
 */
public function getAllMetadata(): array;
```

- **Returns:**
    - An array of all metadata entries, indexed by class name.

---

#### `clearMetadata`

Clears all metadata from memory and the cache.

```php
/**
 * Clear all stored metadata.
 */
public function clearMetadata(): void;
```

- **Usage:**
    - Useful for resetting state during tests or reinitializing metadata after changes.

---

### Configuration and Caching

The `MetadataCollector` uses Symfony's caching system for efficient metadata management. This ensures that the metadata is only calculated once per entity and reused across multiple requests.

#### Cache Key Generation

Each metadata entry is associated with a unique cache key.

```php
/**
 * Generate a cache key for a class name.
 *
 * @param string $className
 * @return string
 */
private function getCacheKey(string $className): string;
```

- **Key Format:**
    - `elastic_entity_metadata_{md5(className)}`

---

### Example Usage

#### Adding Metadata

```php
use Zhortein\ElasticEntityBundle\Metadata\MetadataCollector;

$metadataCollector->addMetadata(Product::class);
```

#### Retrieving Metadata

```php
$metadata = $metadataCollector->getMetadata(Product::class);
if ($metadata) {
    echo 'Class: ' . $metadata['class'];
}
```

#### Clearing Metadata

```php
$metadataCollector->clearMetadata();
```

---

### Best Practices

- Use `addMetadata` to register new entities dynamically.
- Always retrieve metadata using `getMetadata` to leverage caching.
- Clear metadata when making significant changes to entity definitions.

---

[Back](./FEATURES_DOCUMENTATION.md)
