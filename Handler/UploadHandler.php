<?php

namespace Vich\UploaderBundle\Handler;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use Vich\UploaderBundle\Injector\FileInjectorInterface;
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
     * Constructs a new instance of UploaderListener.
     *
     * @param \Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory  The mapping factory.
     * @param \Vich\UploaderBundle\Storage\StorageInterface       $storage  The storage.
     * @param \Vich\UploaderBundle\Injector\FileInjectorInterface $injector The injector.
     */
    public function __construct(PropertyMappingFactory $factory, StorageInterface $storage, FileInjectorInterface $injector)
    {
        $this->factory = $factory;
        $this->storage = $storage;
        $this->injector = $injector;
    }

    /**
     * Checks for file to upload.
     */
    public function upload($object, $mapping)
    {
        if (!$this->factory->hasMapping($object, $mapping)) {
            return;
        }

        $mapping = $this->factory->fromName($object, $mapping);
        $file = $mapping->getFile($object);

        if ($file === null || !($file instanceof UploadedFile)) {
            return;
        }

        $this->storage->upload($object, $mapping);
        $this->injector->injectFile($object, $mapping);
    }

    /**
     * Checks for file to remove before a new upload.
     */
    public function clean($object, $mapping)
    {
        if (!$this->factory->hasMapping($object, $mapping)) {
            return;
        }

        $mapping = $this->factory->fromName($object, $mapping);
        $file = $mapping->getFile($object);

        if ($file === null || !($file instanceof UploadedFile)) {
            return;
        }

        // if there already is a file for the given object, delete it if needed
        if ($mapping->getFileName($object)) {
            $this->storage->remove($object, $mapping);
        }
    }

    public function hydrate($object, $mapping)
    {
        if (!$this->factory->hasMapping($object, $mapping)) {
            return;
        }

        $mapping = $this->factory->fromName($object, $mapping);
        $this->injector->injectFile($object, $mapping);
    }

    public function delete($object, $mapping)
    {
        if (!$this->factory->hasMapping($object, $mapping)) {
            return;
        }

        $mapping = $this->factory->fromName($object, $mapping);
        $this->storage->remove($object, $mapping);
    }
}
