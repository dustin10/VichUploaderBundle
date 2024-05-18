# Events

[Events](https://symfony.com/doc/current/event_dispatcher.html) offer a way to hook into
the flux of other code, in this case into the flux of VichUploaderBundle.

The following is a list of events you can listen to:

| Event name                   | Event constant         | Trigger point                            |
|------------------------------|------------------------|------------------------------------------|
| `vich_uploader.pre_upload`   | `Events::PRE_UPLOAD`   | before a file upload is handled          |
| `vich_uploader.post_upload`  | `Events::POST_UPLOAD`  | right after a file upload is handled     |
| `vich_uploader.pre_inject`   | `Events::PRE_INJECT`   | before a file is injected into an entity |
| `vich_uploader.post_inject`  | `Events::POST_INJECT`  | after a file is injected into an entity  |
| `vich_uploader.pre_remove`   | `Events::PRE_REMOVE`   | before a file is removed                 |
| `vich_uploader.post_remove`  | `Events::POST_REMOVE`  | after a file is removed                  |
| `vich_uploader.upload_error` | `Events::UPLOAD_ERROR` | If file upload failed.                   |
| `vich_uploader.remove_error` | `Events::REMOVE_ERROR` | If file remove failed.                   |

The `vich_uploader.pre_remove` event is cancelable, that means that the actual remove request will not take place,
and you have to take action.

## Example

Create a listener class:

```php
<?php

namespace App\EventListener;

use Vich\UploaderBundle\Event\Event;

class FooListener
{
    public function onVichUploaderPreUpload(Event $event)
    {
        $object = $event->getObject();
        $mapping = $event->getMapping();

        // do your stuff with $object and/or $mapping...
    }

}
```

Configure it in your configuration:

```yaml
# config/services.yaml or app/config/services.yml
services:
    AppBundle\EventListener\FooListener:
        tags:
            - { name: kernel.event_listener, event: vich_uploader.pre_upload }
```

## Error events

The `UPLOAD_ERROR` event happens when writing a file fails, and before an exception is thrown.

If an error occurs while removing a file, then the `REMOVE_ERROR` event gets fired. Failures in removing
files do not trigger any exceptions unless you choose to throw an error in the event handler for the `REMOVE_ERROR`
event.

You can use the event to throw an error when the error occurs:

```php
<?php

namespace App\EventListener;

use Vich\UploaderBundle\Event\ErrorEvent;use Vich\UploaderBundle\Event\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Vich\UploaderBundle\Event\Events;

class RemoveErrorEventListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(){
            return [
            Events::REMOVE_ERROR => 'onUploadError',
        ]       
    }
    public function onUploadError(ErrorEvent $errorEvent)
    {
        $object = $event->getObject();
        $mapping = $event->getMapping();
        $exception = $errorEvent->getThrowable();
        
        throw $exception;
    }

}
```

[Return to the index](../index.md)
