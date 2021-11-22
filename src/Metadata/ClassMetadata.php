<?php

namespace Vich\UploaderBundle\Metadata;

use Metadata\ClassMetadata as BaseClassMetadata;

/**
 * @final
 *
 * @internal
 */
class ClassMetadata extends BaseClassMetadata
{
    /** @var array */
    public $fields = [];

    protected function serializeToArray(): array
    {
        return [
            $this->fields,
            parent::serializeToArray(),
        ];
    }

    protected function unserializeFromArray(array $data): void
    {
        [
            $this->fields,
            $parentData
        ] = $data;

        parent::unserializeFromArray($parentData);
    }
}
