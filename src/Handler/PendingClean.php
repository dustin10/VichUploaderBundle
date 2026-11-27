<?php

namespace Vich\UploaderBundle\Handler;

use Vich\UploaderBundle\Mapping\PropertyMappingInterface;

final readonly class PendingClean
{
    public function __construct(
        public object $object,
        public PropertyMappingInterface $mapping,
        public string $fileName,
        public ?string $uploadDir,
    ) {
    }
}
