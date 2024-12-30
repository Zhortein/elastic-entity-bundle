<?php

namespace Zhortein\ElasticEntityBundle\MessageHandler;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Zhortein\ElasticEntityBundle\Manager\ElasticEntityManager;
use Zhortein\ElasticEntityBundle\Message\CreateMessage;

#[AsMessageHandler(fromTransport: CreateMessage::class)]
readonly class CreateMessageHandler
{
    public function __construct(private ElasticEntityManager $entityManager)
    {
    }

    public function __invoke(CreateMessage $message): void
    {
        $entity = $this->entityManager->hydratePayloadToEntity($message->getClassName(), $message->getPayload());
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}
