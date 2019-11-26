<?php

namespace Vich\UploaderBundle\Event;

use Symfony\Contracts\EventDispatcher\Event as ContractEvent;
use Vich\UploaderBundle\Mapping\PropertyMapping;

/*
 * Base class for upload events.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class Event extends ContractEvent
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
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Accessor to the mapping used to manipulate the object.
     */
    public function getMapping(): PropertyMapping
    {
        return $this->mapping;
    }
}
