<?php

namespace Zhortein\ElasticEntityBundle\Contracts;

interface ElasticEntityInterface
{
    /**
     * Get the unique identifier for the entity.
     */
    public function getId(): string;

    /**
     * Set the unique identifier for the entity.
     */
    public function setId(string $id): self;

    public function isEntityPersisted(): bool;

    public function setEntityPersisted(bool $persisted): self;

    public function isEntityModified(): bool;

    public function setEntityModified(bool $modified): self;

    public function isEntityHavePendingOperation(): bool;

    public function setEntityHavePendingOperation(bool $pending): self;
}
