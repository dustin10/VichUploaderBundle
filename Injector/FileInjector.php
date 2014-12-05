<?php

namespace Vich\UploaderBundle\Injector;

use Symfony\Component\HttpFoundation\File\File;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * FileInjector.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class FileInjector implements FileInjectorInterface
{
    /**
     * @var \Vich\UploaderBundle\Storage\StorageInterface
     */
    protected $storage;

    /**
     * Constructs a new instance of FileInjector.
     *
     * @param \Vich\UploaderBundle\Storage\StorageInterface $storage Storage.
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritDoc}
     */
    public function injectFile($obj, PropertyMapping $mapping)
    {
        $path = $this->storage->resolvePath($obj, $mapping->getFilePropertyName());

        if ($path !== null) {
            $mapping->setFile($obj, new File($path, false));
        }
    }
}
