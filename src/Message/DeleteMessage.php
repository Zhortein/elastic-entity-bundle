<?php

namespace Zhortein\ElasticEntityBundle\Message;

use Zhortein\ElasticEntityBundle\Contracts\ElasticEntityInterface;

readonly class DeleteMessage implements ElasticEntityMessageInterface
{
    /**
     * @param class-string<ElasticEntityInterface> $className
     */
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
