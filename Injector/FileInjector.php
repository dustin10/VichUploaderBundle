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
     * @var StorageInterface
     */
    protected $storage;

    /**
     * Constructs a new instance of FileInjector.
     *
     * @param StorageInterface $storage Storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public function injectFile($obj, PropertyMapping $mapping)
    {
        $path = $this->storage->resolvePath($obj, $mapping->getFilePropertyName());

        if (null !== $path) {
            $mapping->setFile($obj, new File($path, false));
        }
    }
}
