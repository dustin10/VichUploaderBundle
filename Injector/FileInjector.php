<?php

namespace Vich\UploaderBundle\Injector;

use Vich\UploaderBundle\Injector\FileInjectorInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
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
     * Constructs a new instance of FileInjector.
     *
     * @param \Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory The factory.
     */
    public function __construct(PropertyMappingFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritDoc}
     */
    public function injectFiles($obj)
    {
        $mappings = $this->factory->fromObject($obj);
        foreach ($mappings as $mapping) {
            if ($mapping->getInjectOnLoad()) {
                $name = $mapping->getFileNameProperty()->getValue($obj);
                if (is_null($name)) {
                    continue;
                }

                $path = sprintf('%s/%s', $mapping->getUploadDir(), $name);

                $mapping->getProperty()->setValue(
                    $obj,
                    new File($path, false)
                );
            }
        }
    }
}
