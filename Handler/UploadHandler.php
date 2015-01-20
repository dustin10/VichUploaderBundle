<?php

namespace Vich\UploaderBundle\Handler;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;
use Vich\UploaderBundle\Injector\FileInjectorInterface;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * Upload handler.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class UploadHandler extends AbstractHandler
{
    /**
     * @var \Vich\UploaderBundle\Injector\FileInjectorInterface $injector
     */
    protected $injector;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     */
    protected $dispatcher;

    /**
     * Constructs a new instance of UploaderListener.
     *
     * @param \Vich\UploaderBundle\Mapping\PropertyMappingFactory         $factory    The mapping factory.
     * @param \Vich\UploaderBundle\Storage\StorageInterface               $storage    The storage.
     * @param \Vich\UploaderBundle\Injector\FileInjectorInterface         $injector   The injector.
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher The event dispatcher.
     */
    public function __construct(PropertyMappingFactory $factory, StorageInterface $storage, FileInjectorInterface $injector, EventDispatcherInterface $dispatcher)
    {
        parent::__construct($factory, $storage);

        $this->injector = $injector;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Checks for file to upload.
     *
     * @param object $obj       The object.
     * @param string $fieldName The name of the field containing the upload (has to be mapped).
     */
    public function upload($obj, $fieldName)
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

    public function inject($obj, $fieldName)
    {
        $mapping = $this->getMapping($obj, $fieldName);

        $this->dispatch(Events::PRE_INJECT, new Event($obj, $mapping));

        $this->injector->injectFile($obj, $mapping);

        $this->dispatch(Events::POST_INJECT, new Event($obj, $mapping));
    }

    public function clean($obj, $fieldName)
    {
        $mapping = $this->getMapping($obj, $fieldName);

        // nothing uploaded, do not remove anything
        if (!$this->hasUploadedFile($obj, $mapping)) {
            return;
        }

        $this->remove($obj, $fieldName);
    }

    public function remove($obj, $fieldName)
    {
        $mapping = $this->getMapping($obj, $fieldName);
        $oldFilename = $mapping->getFileName($obj);

        // nothing to remove, avoid dispatching useless events
        if (empty($oldFilename)) {
            return;
        }

        $this->dispatch(Events::PRE_REMOVE, new Event($obj, $mapping));

        $this->storage->remove($obj, $mapping);
        $mapping->setFileName($obj, null);

        $this->dispatch(Events::POST_REMOVE, new Event($obj, $mapping));
    }

    protected function dispatch($eventName, Event $event)
    {
        $this->dispatcher->dispatch($eventName, $event);
    }

    protected function hasUploadedFile($obj, PropertyMapping $mapping)
    {
        $file = $mapping->getFile($obj);

        return $file !== null && $file instanceof UploadedFile;
    }
}
