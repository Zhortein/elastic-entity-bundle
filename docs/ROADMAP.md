# ROADMAP

## Current and Future Features for ElasticEntityBundle

### Implemented Features

1. **ElasticEntityManager**:
    - Manage entities stored in Elasticsearch.
    - Provide a Doctrine-like interface for persisting, updating, deleting, and querying entities.

2. **PHP Attributes**:
    - Define entity configuration (`ElasticEntity`, `ElasticField`, `ElasticRelation`).
    - Support for nested and reference relationships.

3. **Automatic Change Detection**:
    - Use snapshots to track and detect changes in entities before flush.

4. **Event System**:
    - Hook into lifecycle events such as `pre_persist`, `post_persist`, `pre_update`, `post_update`, `pre_remove`, and `post_remove`.

5. **Query and Aggregation**:
    - Find entities by ID, criteria, or custom queries.
    - Perform Elasticsearch aggregation queries.

6. **Bulk Operations**:
    - Efficiently handle batch operations (persist, update, delete).

7. **Comprehensive Documentation**:
    - English and French versions.
    - Detailed examples for each feature.

---

### Planned Features

#### 1. **Constraint Support on Fields**
- Leverage Symfony's `Assert` annotations (e.g., `#[Assert\NotBlank]`, `#[Assert\Length]`).
- Enable validation of entities before persistence.
- Seamless integration with Symfony Forms.

#### 2. **Support for Complex Relationships**
- Expand `ElasticRelation` to handle many-to-many relationships.
- Provide tools for cascading operations across related entities.

#### 3. **Transactional Support**
- Simulate transactions for grouped operations.
- Provide rollback mechanisms in case of partial failures.

#### 4. **Data Validation**
- Automatically validate entities against defined constraints during persistence.

#### 5. **Schema Migration**
- Add tools for managing index schema migrations.
- Ensure smooth upgrades and changes to index structure.

#### 6. **Custom Query Builder**
- Allow developers to construct advanced Elasticsearch queries programmatically.

#### 7. **Optimized Performance**
- Introduce caching mechanisms for metadata and frequently accessed data.
- Optimize bulk operations for high-performance scenarios.

#### 8. **Improved Documentation and Tutorials**
- Add detailed guides for common use cases.
- Provide migration paths for developers transitioning from Doctrine.

#### 9. **Multi-Language Support**
- Facilitate indexing and querying of multilingual content.

#### 10. **Integration with Symfony Ecosystem**
- Seamless compatibility with other Symfony bundles (e.g., Security, Messenger).

#### 11. **Test Suite Expansion**
- Comprehensive unit and integration tests to ensure stability.

---

### Priority Tasks

1. **Implement Symfony Constraints on Fields**:
    - Ensure compatibility with Symfony Forms.
    - Provide runtime validation feedback for developers.

2. **Enhance Relationship Management**:
    - Add support for advanced relationships.
    - Ensure consistency and cascading behaviors during flush.

3. **Performance Optimization**:
    - Introduce metadata caching.
    - Optimize snapshot management for large datasets.

4. **Documentation Updates**:
    - Add examples for new features.
    - Improve clarity and accessibility.

---

This roadmap ensures ElasticEntityBundle continues to evolve to meet developer needs and maintain alignment with Symfony best practices.
