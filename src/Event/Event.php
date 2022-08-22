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
    protected bool $cancel = false;

    public function __construct(protected readonly object $object, protected readonly PropertyMapping $mapping)
    {
    }

    /**
     * Accessor to the object being manipulated.
     */
    public function getObject(): object
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

    /**
     * Cancels the execution of the actual request.
     * Only works for the vich_uploader.pre_remove event.
     */
    public function cancel(): void
    {
        $this->cancel = true;
    }

    /**
     * Returns whether further processing of the request should be executed.
     */
    public function isCanceled(): bool
    {
        return $this->cancel;
    }
}
