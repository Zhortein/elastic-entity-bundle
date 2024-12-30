<?php

namespace Zhortein\ElasticEntityBundle\Message;

use Zhortein\ElasticEntityBundle\Contracts\ElasticEntityInterface;

interface ElasticEntityMessageInterface
{
    /**
     * @return class-string<ElasticEntityInterface>
     */
    public function getClassName(): string;

    public function getId(): string;

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array;
}
