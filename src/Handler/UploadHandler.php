<?php

namespace Vich\UploaderBundle\Handler;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;
use Vich\UploaderBundle\Injector\FileInjectorInterface;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * Upload handler.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
final class UploadHandler extends AbstractHandler
{
    public function __construct(
        PropertyMappingFactory $factory,
        StorageInterface $storage,
        protected readonly FileInjectorInterface $injector,
        protected readonly EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($factory, $storage);
    }

    /**
     * Checks for file to upload.
     *
     * @param object $obj       The object
     * @param string $fieldName The name of the field containing the upload (has to be mapped)
     *
     * @throws \Vich\UploaderBundle\Exception\MappingNotFoundException
     */
    public function upload(object $obj, string $fieldName): void
    {
        $mapping = $this->getMapping($obj, $fieldName);

        // nothing to upload
        if (!$this->hasUploadedFile($obj, $mapping)) {
            return;
        }

        $this->dispatch(Events::PRE_UPLOAD, new Event($obj, $mapping));

        $this->storage->upload($obj, $mapping);
        $this->injector->injectFile($obj, $mapping);

        $this->dispatch(Events::POST_UPLOAD, new Event($obj, $mapping));
    }

    public function inject(object $obj, string $fieldName): void
    {
        $mapping = $this->getMapping($obj, $fieldName);

        $this->dispatch(Events::PRE_INJECT, new Event($obj, $mapping));

        $this->injector->injectFile($obj, $mapping);

        $this->dispatch(Events::POST_INJECT, new Event($obj, $mapping));
    }

    public function clean(object $obj, string $fieldName): void
    {
        $mapping = $this->getMapping($obj, $fieldName);

        // nothing uploaded, do not remove anything
        if (!$this->hasUploadedFile($obj, $mapping)) {
            return;
        }

        $this->remove($obj, $fieldName);
    }

    public function remove(object $obj, string $fieldName): void
    {
        $mapping = $this->getMapping($obj, $fieldName);
        $oldFilename = $mapping->getFileName($obj);

        // nothing to remove, avoid dispatching useless events
        if (empty($oldFilename)) {
            return;
        }

        $preEvent = new Event($obj, $mapping);

        $this->dispatch(Events::PRE_REMOVE, $preEvent);

        if ($preEvent->isCanceled()) {
            return;
        }

        $this->storage->remove($obj, $mapping);
        $mapping->erase($obj);

        $this->dispatch(Events::POST_REMOVE, new Event($obj, $mapping));
    }

    protected function dispatch(string $eventName, Event $event): void
    {
        $this->dispatcher->dispatch($event, $eventName);
    }

    protected function hasUploadedFile(object $obj, PropertyMapping $mapping): bool
    {
        $file = $mapping->getFile($obj);

        return $file instanceof UploadedFile || $file instanceof ReplacingFile;
    }
}
