<?php

namespace Vich\UploaderBundle\Adapter;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
interface AdapterInterface
{
    /**
     * Gets the mapped object from an event.
     *
     * @param object $event The event
     *
     * @return object The mapped object
     */
    public function getObjectFromArgs($event);
}
