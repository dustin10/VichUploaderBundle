<?php

declare(strict_types=1);

namespace Vich\UploaderBundle\FileAbstraction;

use Symfony\Component\HttpFoundation\File\File;

/**
 * This class can be used to signal that the given file should be "uploaded" into the Vich-abstraction
 * in cases where it is not possible to construct an `UploadedFile`.
 */
class ReplacingFile extends File
{
    public function __construct(
        string $path,
        bool $checkPath = true,
        private readonly bool $removeReplacedFile = false,
        private readonly bool $removeReplacedFileOnError = false
    ) {
        parent::__construct($path, $checkPath);
    }

    public function getClientOriginalName(): string
    {
        return $this->getFilename();
    }

    public function isRemoveReplacedFile(): bool
    {
        return $this->removeReplacedFile;
    }

    public function isRemoveReplacedFileOnError(): bool
    {
        return $this->removeReplacedFileOnError;
    }
}
