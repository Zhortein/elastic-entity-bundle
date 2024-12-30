# Messenger Integration

## Overview

The `ElasticEntityBundle` leverages Symfony Messenger to handle asynchronous operations like creating, updating, or deleting ElasticEntities. This feature allows developers to decouple business logic and improve scalability.

---

## Structure

### Messages

The following message classes are available for use:

- `CreateMessage`: Represents a request to create a new ElasticEntity.
- `UpdateMessage`: Represents a request to update an existing ElasticEntity.
- `RemoveMessage`: Represents a request to remove an ElasticEntity.

Each message contains:
- `className`: The fully qualified class name of the ElasticEntity.
- `payload`: An array representing the data or changes to be applied.

### Handlers

Each message is associated with a handler that processes the request.

- **CreateHandler**: Handles the creation of ElasticEntities.
- **UpdateHandler**: Handles the update of ElasticEntities.
- **RemoveHandler**: Handles the deletion of ElasticEntities.

These handlers are automatically registered using the `#[AsMessageHandler]` attribute available in Symfony 7+.

---

## Example Usage

### Dispatching Messages

Use Symfony's `MessageBusInterface` to dispatch messages:

```php
use Symfony\Component\Messenger\MessageBusInterface;
use Zhortein\ElasticEntityBundle\Message\CreateMessage;

class ProductService
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function createProduct(): void
    {
        $payload = ['name' => 'Laptop', 'price' => 1000];
        $message = new CreateMessage(Product::class, $payload);

        $this->bus->dispatch($message);
    }
}
```

### Handler Implementation

Handlers for the above messages are already implemented:

#### Example: CreateHandler

```php
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Zhortein\ElasticEntityBundle\Manager\ElasticEntityManager;
use Zhortein\ElasticEntityBundle\Message\CreateMessage;

#[AsMessageHandler]
class CreateHandler
{
    public function __construct(private ElasticEntityManager $entityManager) {}

    public function __invoke(CreateMessage $message): void
    {
        $this->entityManager->persist($message->getPayload());
    }
}
```

---

## Configuration

No additional configuration is required. Handlers are auto-registered via the `#[AsMessageHandler]` attribute.

Ensure your project includes `symfony/messenger` and a configured transport if asynchronous processing is required.

---

## Testing

### Example Test for CreateHandler

```php
use PHPUnit\Framework\TestCase;
use Zhortein\ElasticEntityBundle\Manager\ElasticEntityManager;
use Zhortein\ElasticEntityBundle\Message\CreateMessage;
use Zhortein\ElasticEntityBundle\MessageHandler\CreateHandler;

class CreateHandlerTest extends TestCase
{
    public function testHandlerProcessesCreateMessage(): void
    {
        $entityManagerMock = $this->createMock(ElasticEntityManager::class);
        $entityManagerMock
            ->expects($this->once())
            ->method('persist')
            ->with(['name' => 'Laptop', 'price' => 1000]);

        $handler = new CreateHandler($entityManagerMock);

        $message = new CreateMessage(Product::class, ['name' => 'Laptop', 'price' => 1000]);
        $handler($message);
    }
}
```

---

## Notes

- Use Symfony Messenger's transport configuration to enable asynchronous handling of messages.
- Ensure that entities used in the `payload` comply with the `ElasticEntityInterface`.

---

[Back](./FEATURES_DOCUMENTATION.md)
