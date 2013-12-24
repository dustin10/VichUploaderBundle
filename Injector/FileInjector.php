<?php

namespace Vich\UploaderBundle\Injector;

use Symfony\Component\HttpFoundation\File\File;

use Vich\UploaderBundle\Injector\FileInjectorInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * FileInjector.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class FileInjector implements FileInjectorInterface
{
    /**
     * @var PropertyMappingFactory $factory
     */
    protected $factory;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * Constructs a new instance of FileInjector.
     *
     * @param PropertyMappingFactory $factory The factory.
     * @param StorageInterface       $storage Storage.
     */
    public function __construct(PropertyMappingFactory $factory, StorageInterface $storage)
    {
        $this->factory = $factory;
        $this->storage = $storage;
    }

    /**
     * {@inheritDoc}
     */
    public function injectFiles($obj)
    {
        $mappings = $this->factory->fromObject($obj);
        foreach ($mappings as $mapping) {
            if (!$mapping->getInjectOnLoad()) {
                continue;
            }

            $field = $mapping->getFilePropertyName();
            try {
                $path = $this->storage->resolvePath($obj, $field);
            } catch (\InvalidArgumentException $e) {
                continue;
            }

            $mapping->setFile($obj, new File($path, false));
        }
    }
}
