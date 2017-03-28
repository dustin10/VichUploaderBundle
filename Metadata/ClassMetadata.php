<?php

namespace Vich\UploaderBundle\Metadata;

use Metadata\ClassMetadata as BaseClassMetadata;

class ClassMetadata extends BaseClassMetadata
{
    public $fields = [];

    public function serialize()
    {
        return serialize([
            $this->fields,
            parent::serialize(),
        ]);
    }

    public function unserialize($str)
    {
        list(
            $this->fields,
            $parentStr
            ) = unserialize($str);

        parent::unserialize($parentStr);
    }
}
