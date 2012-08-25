<?php

namespace Vich\UploaderBundle\Injector;

use Vich\UploaderBundle\Injector\FileInjectorInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\File\File;
/**
 * FileInjector.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class FileInjector implements FileInjectorInterface
{
    /**
     * @var \Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory
     */
    protected $factory;

    /**
     * @var \Vich\UploaderBundle\Storage\StorageInterface
     */
    protected $storage;

    /**
     * Constructs a new instance of FileInjector.
     *
     * @param \Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory The factory.
     * @param \Vich\UploaderBundle\Storage\StorageInterface       $storage Storage.
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
            if ($mapping->getInjectOnLoad()) {
                $field = $mapping->getProperty()->getName();
                try {
                    $path = $this->storage->resolvePath($obj, $field);
                } catch (\InvalidArgumentException $e) {
                    continue;
                }

                $mapping->getProperty()->setValue(
                    $obj,
                    new File($path, false)
                );
            }
        }
    }
}
