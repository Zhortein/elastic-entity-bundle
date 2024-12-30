<?php

namespace Zhortein\ElasticEntityBundle\MessageHandler;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Zhortein\ElasticEntityBundle\Manager\ElasticEntityManager;
use Zhortein\ElasticEntityBundle\Message\DeleteMessage;

#[AsMessageHandler(fromTransport: DeleteMessage::class)]
readonly class DeleteMessageHandler
{
    public function __construct(private ElasticEntityManager $entityManager)
    {
    }

    public function __invoke(DeleteMessage $message): void
    {
        $entity = $this->entityManager->find($message->getClassName(), $message->getId());

        if (null !== $entity) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
        }
    }
}
