<?php

namespace Vich\UploaderBundle\Upload;

use Vich\UploaderBundle\Upload\UploaderInterface;
use Vich\UploaderBundle\Model\UploadableInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Uploader.
 * 
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class Uploader implements UploaderInterface
{
    /**
     * @var ContainerInterface $namer
     */
    protected $container;
    
    /**
     * @var array $mappings
     */
    protected $mappings;
    
    /**
     * @var string $webDirName
     */
    protected $webDirName;
    
    /**
     * Constructs a new instance of Uploader.
     * 
     * @param ContainerInterface $container The container.
     * @param array $mappings The mappings.
     * @param string $webDirName The name of the application's web root directory.
     */
    public function __construct(ContainerInterface $container, array $mappings, $webDirName)
    {
        $this->container = $container;
        $this->mappings = $mappings;
        $this->webDirName = $webDirName;
    }
    
    /**
     * {@inheritDoc}
     */
    public function upload(UploadableInterface $uploadable)
    {
        $file = $uploadable->getFile();
        if (null === $file) {
            return;
        }
        
        $uploadDir = $this->getUploadDirForUploadable($uploadable);
        $namer = $this->getNamerForUploadable($uploadable);
        $name = $namer->name($uploadable);
        
        $file->move($uploadDir, $name);
        
        $uploadable->setFileName($name);
    }
    
    /**
     * {@inheritDoc}
     */
    public function remove(UploadableInterface $uploadable)
    {
        if ($this->shouldDeleteFileOnRemove($uploadable)) {
            $dir = $this->getUploadDirForUploadable($uploadable);
            $name = $uploadable->getFileName();
            
            unlink(sprintf('%s/%s', $dir, $name));
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function getPublicPath(UploadableInterface $uploadable)
    {
        $uploadDir = $this->getUploadDirForUploadable($uploadable);
        $index = strpos($uploadDir, $this->webDirName);
        $relDir = substr($uploadDir, $index + strlen($this->webDirName));
        
        return sprintf('%s/%s', $relDir, $uploadable->getFileName());
    }
    
    /**
     * Gets the configured upload directory for the specified class name.
     * 
     * @param UploadableInterface $obj The object.
     * @return string The upload directory.
     */
    protected function getUploadDirForUploadable(UploadableInterface $obj)
    {
        $mapping = $this->getMappingForUploadable($obj);
        
        return $mapping['upload_dir'];
    }
    
    /**
     * Gets the configured namer for the object.
     * 
     * @param UploadableInterface $obj The object.
     * @return NamerInterface The configured namer.
     */
    protected function getNamerForUploadable(UploadableInterface $obj)
    {
        $mapping = $this->getMappingForUploadable($obj);
        
        if ($mapping['namer']) {
            return $this->container->get($mapping['namer']);
        }
        
        return $this->container->get('vich_uploader.namer');
    }
    
    /**
     * Determines if the class is configured to have its file deleted upon 
     * removal.
     * 
     * @param UploadableInterface $obj The object.
     * @return string True if the file should be deleted, false otherwise.
     */
    protected function shouldDeleteFileOnRemove(UploadableInterface $obj)
    {
        $mapping = $this->getMappingForUploadable($obj);
        
        return $mapping['delete_on_remove'];
    }
    
    /**
     * Gets the configured mappings for the specified object.
     * 
     * @param UploadableInterface $obj The object.
     * @return array The mappings for the specified object.
     */
    protected function getMappingForUploadable(UploadableInterface $obj)
    {
        foreach (array_keys($this->mappings) as $class) {
            if (get_class($obj) === $class || is_subclass_of($obj, $class)) {
                return $this->mappings[$class];
            }
        }
        
        throw new \InvalidArgumentException(sprintf(
            'No mapping found for class: "%s"',
            $class
        ));
    }
}
