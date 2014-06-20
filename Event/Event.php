<?php

namespace Vich\UploaderBundle\Event;

use Symfony\Component\EventDispatcher\Event as BaseEvent;

use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * Base class for upload events.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class Event extends BaseEvent
{
    protected $object;
    protected $mapping;

    public function __construct($object, PropertyMapping $mapping)
    {
        $this->object = $object;
        $this->mapping = $mapping;
    }

    /**
     * Accessor to the object being manipulated.
     *
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Accessor to the mapping used to manipulate the object.
     *
     * @return \Vich\UploaderBundle\Mapping\PropertyMapping
     */
    public function getMapping()
    {
        return $this->mapping;
    }
}
