<?php

namespace Vich\UploaderBundle\Upload;

use Vich\UploaderBundle\Upload\UploaderInterface;
use Vich\UploaderBundle\Model\UploadableInterface;
use Vich\UploaderBundle\Naming\NamerInterface;

/**
 * Uploader.
 * 
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class Uploader implements UploaderInterface
{
    /**
     * @var NamerInterface $namer
     */
    protected $namer;
    
    /**
     * @var array $mappings
     */
    protected $mappings;
    
    /**
     * Constructs a new instance of Uploader.
     * 
     * @param NamerInterface $namer The namer.
     * @param array $mappings The mappings.
     */
    public function __construct(NamerInterface $namer, array $mappings)
    {
        $this->namer = $namer;
        $this->mappings = $mappings;
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
        
        // todo: deal with Proxy
        $class = get_class($uploadable);
        
        $uploadDir = $this->getUploadDirForClass($class);
        $name = $this->namer->name($uploadable);
        
        $file->move($uploadDir, $name);
        
        $uploadable->setFileName($name);
    }
    
    /**
     * {@inheritDoc}
     */
    public function remove(UploadableInterface $uploadable)
    {
        // todo: deal with Proxy
        $class = get_class($uploadable);
        
        if ($this->shouldDeleteFileOnRemove($class)) {
            $dir = $this->getUploadDirForClass($class);
            $name = $uploadable->getFileName();
            
            unlink(sprintf('%s/%s', $dir, $name));
        }
    }
    
    /**
     * Gets the configured upload directory for the specified class name.
     * 
     * @param string $class The class name.
     * @return string The upload directory.
     */
    protected function getUploadDirForClass($class)
    {
        if (!isset($this->mappings[$class])) {
            throw new \InvalidArgumentException(sprintf(
                'No upload directory mapping found for class: "%s"',
                $class
            ));
        }
        
        return $this->mappings[$class]['upload_dir'];
    }
    
    /**
     * Determines if the class is configured to have its file deleted upon 
     * removal.
     * 
     * @param string $class The class name.
     * @return string True if the file should be deleted, false otherwise.
     */
    protected function shouldDeleteFileOnRemove($class)
    {
        if (!isset($this->mappings[$class])) {
            throw new \InvalidArgumentException(sprintf(
                'No delete on remove mapping found for class: "%s"',
                $class
            ));
        }
        
        return $this->mappings[$class]['delete_on_remove'];
    }
}
