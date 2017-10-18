Events
======

[Events](https://symfony.com/doc/current/event_dispatcher.html) offer a way to hook into
the flux of other code, in this case into the flux of VichUploaderBundle.

The following is a list of events you can listen to:

| Event name | Event constant | Trigger point|
|------------|----------------|--------------|
|`vich_uploader.pre_upload`|`Events::PRE_UPLOAD`|before a file upload is handled|
|`vich_uploader.post_upload`|`Events::POST_UPLOAD`|right after a file upload is handled|
|`vich_uploader.pre_inject`|`Events::PRE_INJECT`|before a file is injected into an entity|
|`vich_uploader.post_inject`|`Events::POST_INJECT`|after a file is injected into an entity|
|`vich_uploader.pre_remove`|`Events::PRE_REMOVE`|before a file is removed|
|`vich_uploader.post_remove`|`Events::POST_REMOVE`|after a file is removed|

Example
-------

Create a listener class:

```php
<?php

namespace AppBundle\EventListener;

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
# app/config/services.yml
services:
    app.foo_listener:
        class: AppBundle\EventListener\FooListener
        tags:
            - { name: kernel.event_listener, event: vich_uploader.pre_upload }
```

[Return to the index](index.md)
