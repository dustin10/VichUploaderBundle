<?php

namespace Vich\UploaderBundle\Adapter;

/**
 * AdapterInterface.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
interface AdapterInterface
{
    /**
     * Gets the mapped object from an event.
     *
     * @param  object $event The event argument.
     * @return object The mapped object.
     */
    public function getObjectFromEvent($event);

    /**
     * Recomputes the change set for the given object.
     *
     * @param object $event The event arguments.
     */
    public function recomputeChangeSet($event);

    /**
     * Gets the reflection class for the object, taking
     * proxies into account.
     *
     * @param  object           $object The object.
     * @return \ReflectionClass The reflection class.
     */
    public function getReflectionClass($object);
}
