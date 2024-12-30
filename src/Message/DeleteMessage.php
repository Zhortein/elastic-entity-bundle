<?php

namespace Zhortein\ElasticEntityBundle\Message;

class DeleteMessage implements ElasticEntityMessageInterface
{
    public function __construct(
        private string $className,
        private string $id,
    ) {
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPayload(): array
    {
        return [];
    }
}
