<?php

namespace Vich\UploaderBundle\EventListener;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Vich\UploaderBundle\Upload\UploaderInterface;
use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\Driver\AnnotationDriver;

/**
 * UploaderListener.
 * 
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class UploaderListener implements EventSubscriber
{
    /**
     * @var AdapterInterface $adapter
     */
    protected $adapter;

    /**
     * @var AnnotationDriver $driver
     */
    protected $driver;
    
    /**
     * @var UploaderInterface $namer
     */
    protected $uploader;
    
    /**
     * Constructs a new instance of UploaderListener.
     *
     * @param \Vich\UploaderBundle\Adapter\AdapterInterface $adapter The adapter.
     * @param \Vich\UploaderBundle\Driver\AnnotationDriver $driver The driver.
     * @param \Vich\UploaderBundle\Upload\UploaderInterface $uploader The uploader.
     */
    public function __construct(AdapterInterface $adapter, AnnotationDriver $driver, UploaderInterface $uploader)
    {
        $this->adapter = $adapter;
        $this->driver = $driver;
        $this->uploader = $uploader;
    }
    
    /**
     * The events the listener is subscribed to.
     * 
     * @return array The array of events.
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate',
            'postRemove',
            'postLoad'
        );
    }
    
    /**
     * Checks for for file to upload.
     *
     * @param \Doctrine\Common\EventArgs $args The event arguments.
     */
    public function prePersist(EventArgs $args)
    {
        $obj = $this->adapter->getObjectFromArgs($args);
        
        if ($this->isUploadable($obj)) {
            $this->uploader->upload($obj);
        }
    }

    /**
     * Update the file and file name if necessary.
     *
     * @param EventArgs $args The event arguments.
     */
    public function preUpdate(EventArgs $args)
    {
        $obj = $this->adapter->getObjectFromArgs($args);
        
        if ($this->isUploadable($obj)) {
            $this->uploader->upload($obj);
            
            $this->adapter->recomputeChangeSet($args);
        }
    }
    
    /**
     * Removes the file if necessary.
     * 
     * @param EventArgs $args The event arguments.
     */
    public function postRemove(EventArgs $args)
    {
        $obj = $this->adapter->getObjectFromArgs($args);
        
        if ($this->isUploadable($obj)) {
            $this->uploader->remove($obj);
        }
    }

    /**
     * Populates uploadable fields from filename properties
     * if necessary.
     *
     * @param \Doctrine\Common\EventArgs $args
     */
    public function postLoad(EventArgs $args)
    {
        $obj = $this->adapter->getObjectFromArgs($args);
        if ($this->isUploadable($obj)) {
            $this->uploader->populateUploadableFields($obj);
        }
    }
    
    /**
     * Tests if the object is Uploadable.
     * 
     * @param object $obj The object.
     * @return boolean True if uploadable, false otherwise.
     */
    protected function isUploadable($obj)
    {
        $class = $this->adapter->getReflectionClass($obj);

        return null !== $this->driver->readUploadable($class);
    }
}
