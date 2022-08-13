<?php

namespace Vich\UploaderBundle\Metadata;

use Metadata\ClassMetadata as BaseClassMetadata;

/**
 * @internal
 */
final class ClassMetadata extends BaseClassMetadata
{
    public array $fields = [];

    public function serialize(): string
    {
        return \serialize([
            $this->fields,
            parent::serialize(),
        ]);
    }

    public function unserialize($str): void
    {
        [
            $this->fields,
            $parentStr
            ] = \unserialize($str);

        parent::unserialize($parentStr);
    }
}
