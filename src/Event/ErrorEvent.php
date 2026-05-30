<?php

namespace Vich\UploaderBundle\Event;

use Vich\UploaderBundle\Mapping\PropertyMapping;

class ErrorEvent extends Event
{
    public function __construct(object $object, PropertyMapping $mapping, private readonly \Throwable $throwable)
    {
        parent::__construct($object, $mapping);
    }

    public function getThrowable(): \Throwable
    {
        return $this->throwable;
    }
}
