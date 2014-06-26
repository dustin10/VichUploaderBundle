<?php

namespace Vich\UploaderBundle\Event;

use Symfony\Component\EventDispatcher\Event as BaseEvent;

/**
 * Base class for upload events.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class Event extends BaseEvent
{
    protected $object;

    public function __construct($object)
    {
        $this->object = $object;
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
}
