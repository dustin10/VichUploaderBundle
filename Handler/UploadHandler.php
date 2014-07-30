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
class UploadHandler
{
    /**
     * @var \Vich\UploaderBundle\Mapping\PropertyMappingFactory
     */
    protected $factory;

    /**
     * @var \Vich\UploaderBundle\Storage\StorageInterface $storage
     */
    protected $storage;

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
        $this->factory = $factory;
        $this->storage = $storage;
        $this->injector = $injector;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Checks for file to upload.
     */
    public function upload($obj, $mapping)
    {
        $mapping = $this->factory->fromName($obj, $mapping);

        // nothing to upload
        if (!$this->hasUploadedFile($obj, $mapping)) {
            return;
        }

        $this->dispatch(Events::PRE_UPLOAD, new Event($obj, $mapping));

        $this->storage->upload($obj, $mapping);
        $this->injector->injectFile($obj, $mapping);

        $this->dispatch(Events::POST_UPLOAD, new Event($obj, $mapping));
    }

    public function inject($obj, $mapping)
    {
        $mapping = $this->factory->fromName($obj, $mapping);

        $this->dispatch(Events::PRE_INJECT, new Event($obj, $mapping));

        $this->injector->injectFile($obj, $mapping);

        $this->dispatch(Events::POST_INJECT, new Event($obj, $mapping));
    }

    public function clean($obj, $mappingName)
    {
        $mapping = $this->factory->fromName($obj, $mappingName);

        // nothing uploaded, do not remove anything
        if (!$this->hasUploadedFile($obj, $mapping)) {
            return;
        }

        $this->remove($obj, $mappingName);
    }

    public function remove($obj, $mapping)
    {
        $mapping = $this->factory->fromName($obj, $mapping);

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

        return $file !== null & $file instanceof UploadedFile;
    }
}
