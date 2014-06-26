<?php

namespace Vich\UploaderBundle\EventListener\Propel;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\Handler\UploadHandler;

/**
 * BaseListener
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
abstract class BaseListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    protected $mapping;

    /**
     * @var AdapterInterface $adapter
     */
    protected $adapter;

    /**
     * @var UploaderHandler $handler
     */
    protected $handler;

    /**
     * Constructs a new instance of BaseListener.
     *
     * @param string           $mapping The mapping name.
     * @param AdapterInterface $adapter The adapter.
     * @param UploaderHandler  $handler The upload handler.
     */
    public function __construct($mapping, AdapterInterface $adapter, UploadHandler $handler)
    {
        $this->mapping = $mapping;
        $this->adapter = $adapter;
        $this->handler = $handler;
    }
}
