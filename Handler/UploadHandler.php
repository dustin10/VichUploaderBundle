<?php

namespace Vich\UploaderBundle\Handler;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;
use Vich\UploaderBundle\Injector\FileInjectorInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * Upload handler.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class UploadHandler
{
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
     * @param \Vich\UploaderBundle\Storage\StorageInterface                 $storage    The storage.
     * @param \Vich\UploaderBundle\Injector\FileInjectorInterface           $injector   The injector.
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface   $dispatcher The event dispatcher.
     */
    public function __construct(StorageInterface $storage, FileInjectorInterface $injector, EventDispatcherInterface $dispatcher)
    {
        $this->storage = $storage;
        $this->injector = $injector;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Checks for file to upload.
     */
    public function handleUpload($obj)
    {
        $this->dispatch(Events::PRE_UPLOAD, new Event($obj));

        $this->storage->upload($obj);
        $this->injector->injectFiles($obj);

        $this->dispatch(Events::POST_UPLOAD, new Event($obj));
    }

    public function handleHydration($obj)
    {
        $this->dispatch(Events::PRE_INJECT, new Event($obj));

        $this->injector->injectFiles($obj);

        $this->dispatch(Events::POST_INJECT, new Event($obj));
    }

    public function handleDeletion($obj)
    {
        $this->dispatch(Events::PRE_REMOVE, new Event($obj));

        $this->storage->remove($obj);

        $this->dispatch(Events::POST_REMOVE, new Event($obj));
    }

    protected function dispatch($eventName, Event $event)
    {
        $this->dispatcher->dispatch($eventName, $event);
    }
}
