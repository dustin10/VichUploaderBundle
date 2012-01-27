<?php

namespace Vich\UploaderBundle\Upload;

use Vich\UploaderBundle\Upload\UploaderInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

/**
 * Uploader.
 * 
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class Uploader implements UploaderInterface
{
    /**
     * @var PropertyMappingFactory $container
     */
    protected $factory;
    
    /**
     * @var string $webDirName
     */
    protected $webDirName;

    /**
     * Constructs a new instance of Uploader.
     *
     * @param \Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory The mapping factory.
     * @param $webDirName The name of the application's public directory.
     */
    public function __construct(PropertyMappingFactory $factory, $webDirName)
    {
        $this->factory = $factory;
        $this->webDirName = $webDirName;
    }
    
    /**
     * {@inheritDoc}
     */
    public function upload($obj)
    {
        $mappings = $this->factory->fromObject($obj);
        foreach($mappings as $mapping) {
            $file = $mapping->getPropertyValue($obj);
            if (is_null($file)) {
                continue;
            }

            if ($mapping->hasNamer()) {
                $name = $mapping->getNamer()->name($obj);
            } else {
                $name = $file->getClientOriginalName();
            }

            $file->move($mapping->getUploadDir(), $name);

            $mapping->getFileNameProperty()->setValue($obj, $name);
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function remove($obj)
    {
        $mappings = $this->factory->fromObject($obj);
        foreach($mappings as $mapping) {
            if ($mapping->getDeleteOnRemove()) {
                $name = $mapping->getFileNameProperty()->getValue($obj);

                unlink(sprintf('%s/%s', $mapping->getUploadDir(), $name));
            }
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function getPublicPath($obj, $field)
    {
        $mapping = $this->factory->fromField($obj, $field);
        if (null === $mapping) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to find uploadable field named: "%s"', $field
            ));
        }

        $uploadDir = $mapping->getUploadDir();
        $index = strpos($uploadDir, $this->webDirName);
        $relDir = substr($uploadDir, $index + strlen($this->webDirName));

        return sprintf('%s/%s', $relDir, $mapping->getFilenameProperty()->getValue($obj));
    }
}
