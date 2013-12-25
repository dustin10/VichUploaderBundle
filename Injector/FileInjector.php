<?php

namespace Vich\UploaderBundle\Injector;

use Symfony\Component\HttpFoundation\File\File;

use Vich\UploaderBundle\Injector\FileInjectorInterface;
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
     * @var StorageInterface
     */
    protected $storage;

    /**
     * Constructs a new instance of FileInjector.
     *
     * @param StorageInterface $storage Storage.
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritDoc}
     */
    public function injectFiles($object, PropertyMapping $mapping)
    {
        $field = $mapping->getFilePropertyName();

        try {
            $path = $this->storage->resolvePath($object, $field);
        } catch (\InvalidArgumentException $e) {
            return;
        }

        $mapping->setFile($object, new File($path, false));
    }
}
