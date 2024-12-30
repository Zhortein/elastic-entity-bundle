<?php

namespace Zhortein\ElasticEntityBundle\Message;

interface ElasticEntityMessageInterface
{
    public function getClassName(): string;

    public function getId(): string;

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array;
}
