<?php

namespace Vich\UploaderBundle\Storage;

/**
 * Value object representing a file in storage.
 */
final class StoredFile
{
    /**
     * @param string   $path           File path relative to mapping root
     * @param int|null $lastModifiedAt Unix timestamp of the last modification, or null if unavailable
     */
    public function __construct(
        public string $path,
        public ?int $lastModifiedAt = null
    ) {
    }
}
