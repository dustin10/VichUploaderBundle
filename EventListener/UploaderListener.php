<?php

namespace Vich\UploaderBundle\EventListener;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Vich\UploaderBundle\Upload\UploaderInterface;
use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\Model\UploadableInterface;

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
     * @var UploaderInterface $namer
     */
    protected $uploader;
    
    /**
     * Constructs a new instance of UploaderListener.
     * 
     * @param AdapterInterface $adapter The adapter.
     * @param UploaderInterface $uploader The uploader.
     */
    public function __construct(AdapterInterface $adapter, UploaderInterface $uploader)
    {
        $this->adapter = $adapter;
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
            'postRemove'
        );
    }
    
    /**
     * Checks for for file to upload.
     *
     * @param EventArgs $args The event arguments.
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
        
        // todo: delete file if configured
    }
    
    /**
     * Tests if the object implements the UploadableInterface.
     * 
     * @param object $obj The object.
     * @return boolean True if uploadable, false otherwise.
     */
    protected function isUploadable($obj)
    {
        return $obj instanceof UploadableInterface;
    }
}
