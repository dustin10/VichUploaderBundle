<?php

namespace Vich\UploaderBundle\Metadata;

use Metadata\ClassMetadata as BaseClassMetadata;

class ClassMetadata extends BaseClassMetadata
{
    public $fields = array();

    public function serialize()
    {
        return serialize(array(
            $this->name,
            $this->methodMetadata,
            $this->propertyMetadata,
            $this->fileResources,
            $this->createdAt,
            $this->fields
        ));
    }

    public function unserialize($str)
    {
        list(
            $this->name,
            $this->methodMetadata,
            $this->propertyMetadata,
            $this->fileResources,
            $this->createdAt,
            $this->fields
        ) = unserialize($str);

        $this->reflection = new \ReflectionClass($this->name);
    }
}
