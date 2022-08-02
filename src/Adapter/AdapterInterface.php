<?php

namespace Vich\UploaderBundle\Adapter;

use Doctrine\Persistence\Event\LifecycleEventArgs;

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
}
