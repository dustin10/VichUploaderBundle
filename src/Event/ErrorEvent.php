<?php

namespace Vich\UploaderBundle\Event;

use Vich\UploaderBundle\Mapping\PropertyMappingInterface;

class ErrorEvent extends Event
{
    private \Throwable $throwable;

    public function __construct(object $object, PropertyMappingInterface $mapping, \Throwable $throwable)
    {
        parent::__construct($object, $mapping);
        $this->throwable = $throwable;
    }

    public function getThrowable(): \Throwable
    {
        return $this->throwable;
    }
}
