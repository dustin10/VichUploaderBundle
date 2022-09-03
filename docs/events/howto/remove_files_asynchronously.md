# Remove files asynchronously

To remove files asynchronously, ensure you have the [Messenger](https://symfony.com/doc/current/messenger.html)
installed.
The messenger allows you to run tasks asynchronously, in our example the file removal.

**Note**:

> We recommend you to create a message and message handler for each mapping.
> Use `getMappingName` to send the right message in the event subscriber.

**Important**:

> Do not transfer the whole object and mapping or the whole event in the message.
> The `UploadedFile` which will be stored in the object while uploading is not serializable.

Create a message containing the filename of the file to be deleted.

```php
<?php

namespace App\Message;

class RemoveProductImageMessage
{
    private string $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

}
```

Create a message handler that will do the actual removal.

```php
<?php

namespace App\MessageHandler;

use App\Message\RemoveProductImageMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RemoveProductImageMessageHandler implements MessageHandlerInterface
{
    public function __invoke(RemoveProductImageMessage $message): void
    {
        $filename = $message->getFilename();

        // delete your file according to your mapping configuration
    }

}
```

Create a event subscriber that will cancel the remove request and dispatch a remove message.

```php
<?php

namespace App\EventSubscriber;

use App\Message\RemoveProductImageMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;

class RemoveFileEventSubscriber implements EventSubscriberInterface
{

    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::PRE_REMOVE => ['onPreRemove'],
        ];
    }

    public function onPreRemove(Event $event): void
    {
        $mapping = $event->getMapping();
        $mappingName = $mapping->getMappingName();

        if ('products' === $mappingName) {
            $this->dispatch(RemoveProductImageMessage::class, $event);
        }
    }

    private function dispatch(string $messageClass, Event $event): void
    {
        $event->cancel();

        $object = $event->getObject();

        $mapping = $event->getMapping();
        $filename = $mapping->getFileName($object);

        $message = new $messageClass($filename);
        $this->messageBus->dispatch($message);
    }

}
```

## That was it!

Check out the docs for information on how to use the bundle! [Return to the index.](/docs/index.md)
