<?php

namespace Vich\UploaderBundle\Adapter;

use Doctrine\Common\EventArgs;

/**
 * AdapterInterface.
 * 
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
interface AdapterInterface
{
    /**
     * Gets the mapped object from the event arguments.
     * 
     * @param EventArgs $e The event arguments.
     * @return object The mapped object.
     */
    function getObjectFromArgs(EventArgs $e);
    
    /**
     * Recomputes the change set for the object.
     * 
     * @param EventArgs $e The event arguments.
     */
    function recomputeChangeSet(EventArgs $e);
}
