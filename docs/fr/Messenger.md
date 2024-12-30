# Fonctionnalité Messenger

## Vue d'ensemble

Le support de **Symfony Messenger** permet d'ajouter de la robustesse et de l'asynchronisme à la gestion des entités ElasticSearch. Vous pouvez intégrer facilement des messages pour persister, mettre à jour ou supprimer des entités ElasticSearch.

Cette fonctionnalité fournit des **handlers** qui gèrent automatiquement les messages envoyés à l'aide de Messenger, et permet une meilleure gestion des charges importantes ou des traitements longs.

[Retour au sommaire](./FEATURES_DOCUMENTATION_fr.md)

---

## Structure de base des messages

Les messages doivent implémenter une interface dédiée et peuvent être étendus selon vos besoins. Voici un exemple de structure de base pour les messages :

### Interface `ElasticEntityMessageInterface`

```php
namespace App\Message;

interface ElasticEntityMessageInterface
{
    /**
     * Retourne les données du message.
     *
     * @return array<string, mixed> Structure de la charge utile (payload).
     */
    public function getPayload(): array;
}
```

### Exemple de message : `PersistEntityMessage`

```php
namespace App\Message;

use App\Message\ElasticEntityMessageInterface;

class PersistEntityMessage implements ElasticEntityMessageInterface
{
    public function __construct(private array $payload) {}

    public function getPayload(): array
    {
        return $this->payload;
    }
}
```

[Retour au sommaire](./FEATURES_DOCUMENTATION_fr.md)

---

## Configuration des handlers

Les handlers sont automatiquement détectés via l'attribut `#[AsMessageHandler]`. Chaque handler est responsable d'une tâche spécifique, telle que la persistance, la suppression ou la mise à jour d'une entité ElasticSearch.

### Exemple de Handler : `PersistEntityHandler`

```php
namespace App\MessageHandler;

use App\Message\PersistEntityMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Zhortein\ElasticEntityBundle\Manager\ElasticEntityManager;

#[AsMessageHandler]
class PersistEntityHandler
{
    public function __construct(private ElasticEntityManager $entityManager) {}

    public function __invoke(PersistEntityMessage $message): void
    {
        $payload = $message->getPayload();

        // Convertir le payload en entité ElasticSearch
        $entity = $this->convertPayloadToEntity($payload);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    private function convertPayloadToEntity(array $payload): object
    {
        // Implémentation de la conversion du payload en entité ElasticSearch
    }
}
```

[Retour au sommaire](./FEATURES_DOCUMENTATION_fr.md)

---

## Gestion des messages

Pour utiliser la fonctionnalité, il suffit de dispatcher un message compatible via le composant Messenger de Symfony.

### Exemple de dispatch

```php
use App\Message\PersistEntityMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class SomeService
{
    public function __construct(private MessageBusInterface $messageBus) {}

    public function handle(array $payload): void
    {
        $message = new PersistEntityMessage($payload);
        $this->messageBus->dispatch($message);
    }
}
```

--- 

[Retour](./FEATURES_DOCUMENTATION.md)
