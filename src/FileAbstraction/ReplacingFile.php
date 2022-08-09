<?php

declare(strict_types=1);

namespace Vich\UploaderBundle\FileAbstraction;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * This class can be used to signal that the given file should be "uploaded" into the Vich-abstraction
 * in cases where it is not possible to construct an `UploadedFile`.
 */
class ReplacingFile extends File
{
    public function getClientOriginalName(): string
    {
        return $this->getFilename();
    }
}
