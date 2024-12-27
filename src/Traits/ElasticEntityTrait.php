<?php

namespace Zhortein\ElasticEntityBundle\Traits;

trait ElasticEntityTrait
{
    private string $id = '';
    private bool $entityPersisted = false;
    private bool $entityModified = false;
    private bool $entityHavePendingOperation = false;

    public function isEntityPersisted(): bool
    {
        return $this->entityPersisted;
    }

    public function setEntityPersisted(bool $entityPersisted): self
    {
        $this->entityPersisted = $entityPersisted;

        return $this;
    }

    public function isEntityModified(): bool
    {
        return $this->entityModified;
    }

    public function setEntityModified(bool $entityModified): self
    {
        $this->entityModified = $entityModified;

        return $this;
    }

    public function isEntityHavePendingOperation(): bool
    {
        return $this->entityHavePendingOperation;
    }

    public function setEntityHavePendingOperation(bool $pending): self
    {
        $this->entityHavePendingOperation = $pending;

        return $this;
    }

    public function getId(): string
    {
        return $this->id ?? '';
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }
}
