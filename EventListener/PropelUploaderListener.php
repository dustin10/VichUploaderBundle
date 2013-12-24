<?php

namespace Vich\UploaderBundle\EventListener;

use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\Handler\UploadHandler;

/**
 * PropelUploaderListener.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class PropelUploaderListener implements EventSubscriberInterface
{
    /**
     * @var AdapterInterface $adapter
     */
    protected $adapter;

    /**
     * @var UploaderHandler $handler
     */
    protected $handler;

    /**
     * Constructs a new instance of UploaderListener.
     *
     * @param AdapterInterface $adapter The adapter.
     * @param UploaderHandler  $handler The upload handler.
     */
    public function __construct(AdapterInterface $adapter, UploadHandler $handler)
    {
        $this->adapter = $adapter;
        $this->handler = $handler;
    }

    /**
     * The events the listener is subscribed to.
     *
     * @return array The array of events.
     */
    public static function getSubscribedEvents()
    {
        return array(
            'propel.pre_insert'     => 'onUpload',
            'propel.pre_update'     => 'onUpload',
            'propel.post_delete'    => 'onDelete',
            'propel.construct'      => 'onConstruct',
        );
    }

    /**
     * Update the file and file name if necessary.
     *
     * @param GenericEvent $event The event.
     */
    public function onUpload(GenericEvent $event)
    {
        $obj = $this->adapter->getObjectFromEvent($event);
        $this->handler->handleUpload($obj);
    }

    /**
     * Populates uploadable fields from filename properties.
     *
     * @param GenericEvent $event The event.
     */
    public function onConstruct(GenericEvent $event)
    {
        $obj = $this->adapter->getObjectFromEvent($event);
        $this->handler->handleHydration($obj);
    }

    /**
     * Removes the file when the object is deleted.
     *
     * @param GenericEvent $event The event.
     */
    public function onDelete(GenericEvent $event)
    {
        $obj = $this->adapter->getObjectFromEvent($event);
        $this->handler->handleDeletion($obj);
    }
}
