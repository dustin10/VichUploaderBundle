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
     * @var FileInjectorInterface
     */
    protected $injector;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var RemoveHandler
     */
    protected $removeHandler;

    public function __construct(
        PropertyMappingFactory $factory,
        StorageInterface $storage,
        FileInjectorInterface $injector,
        EventDispatcherInterface $dispatcher,
        RemoveHandler $removeHandler
    )
    {
        parent::__construct($factory, $storage);

        $this->injector = $injector;
        $this->dispatcher = $dispatcher;
        $this->removeHandler = $removeHandler;
    }

    /**
     * Checks for file to upload.
     *
     * @param object $obj       The object
     * @param string $fieldName The name of the field containing the upload (has to be mapped)
     *
     * @throws \Vich\UploaderBundle\Exception\MappingNotFoundException
     */
    public function upload($obj, string $fieldName): void
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

    public function inject($obj, string $fieldName): void
    {
        $mapping = $this->getMapping($obj, $fieldName);

        $this->dispatch(Events::PRE_INJECT, new Event($obj, $mapping));

        $this->injector->injectFile($obj, $mapping);

        $this->dispatch(Events::POST_INJECT, new Event($obj, $mapping));
    }

    public function clean($obj, string $fieldName): void
    {
        $mapping = $this->getMapping($obj, $fieldName);

        // nothing uploaded, do not remove anything
        if (!$this->hasUploadedFile($obj, $mapping)) {
            return;
        }

        $this->removeHandler->remove($obj, $fieldName);
    }

    /**
     * Removes file from filesystem and objects mapping
     *
     * @param $obj
     * @param string $fieldName
     */
    public function remove($obj, string $fieldName): void
    {
        $this->removeHandler->remove($obj, $fieldName);
    }

    /**
     * Adds file to queue to be removed from filesystem during postFlush event
     *
     * @param $obj
     * @param string $fieldName
     */
    public function removeQueued($obj, string $fieldName): void
    {
        $this->removeHandler->addToQueue($obj, $fieldName);
    }

    /**
     * Removes all files in queue. Will return array of updated entities to be persisted
     *
     * @return array list of updated entities
     */
    public function removeFilesInQueue(): array
    {
        return $this->removeHandler->removeFilesInQueue();
    }

    protected function dispatch(string $eventName, Event $event): void
    {
        $this->dispatcher->dispatch($eventName, $event);
    }

    protected function hasUploadedFile($obj, PropertyMapping $mapping): bool
    {
        $file = $mapping->getFile($obj);

        return null !== $file && $file instanceof UploadedFile;
    }
}
