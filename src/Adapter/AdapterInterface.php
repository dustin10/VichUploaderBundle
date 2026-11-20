<?php

namespace Vich\UploaderBundle\Adapter;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\ManagerEventArgs;

/**
 * AdapterInterface.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 *
 * @internal
 */
interface AdapterInterface
{
    /**
     * Recomputes the change set for the object.
     */
    public function recomputeChangeSet(LifecycleEventArgs $event): void;

    /**
     * Returns the objects whose changes will be computed by the upcoming flush.
     *
     * @return iterable<object>
     */
    public function getObjectsToFlush(ManagerEventArgs $event, bool $includeInsertions): iterable;
}
