<?php

namespace Zhortein\ElasticEntityBundle\Message;

use Zhortein\ElasticEntityBundle\Contracts\ElasticEntityInterface;

readonly class CreateMessage implements ElasticEntityMessageInterface
{
    /**
     * @param class-string<ElasticEntityInterface> $className
     * @param array<string, mixed>                 $payload
     */
    public function __construct(
        private string $className,
        private string $id,
        private array $payload,
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
        return $this->payload;
    }
}
