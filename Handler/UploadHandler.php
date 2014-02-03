<?php

namespace Vich\UploaderBundle\Handler;

use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Injector\FileInjectorInterface;

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
     * Constructs a new instance of UploaderListener.
     *
     * @param \Vich\UploaderBundle\Storage\StorageInterface       $storage  The storage.
     * @param \Vich\UploaderBundle\Injector\FileInjectorInterface $injector The injector.
     */
    public function __construct(StorageInterface $storage, FileInjectorInterface $injector)
    {
        $this->storage = $storage;
        $this->injector = $injector;
    }

    /**
     * Checks for file to upload.
     */
    public function handleUpload($obj)
    {
        $this->storage->upload($obj);
        $this->injector->injectFiles($obj);
    }

    public function handleHydration($obj)
    {
        $this->injector->injectFiles($obj);
    }

    public function handleDeletion($obj)
    {
        $this->storage->remove($obj);
    }
}
