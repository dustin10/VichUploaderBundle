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
    protected bool $removeReplacedFile;
    protected bool $removeReplacedFileOnError;

    public function __construct(
        string $path,
        bool $checkPath = true,
        bool $removeReplacedFile = false,
        bool $removeReplacedFileOnError = false
    ) {
        parent::__construct($path, $checkPath);

        $this->removeReplacedFile = $removeReplacedFile;
        $this->removeReplacedFileOnError = $removeReplacedFileOnError;
    }

    public function getClientOriginalName(): string
    {
        return $this->getFilename();
    }

    public function isRemoveReplacedFile(): bool
    {
        return $this->removeReplacedFile;
    }

    public function setRemoveReplacedFile(bool $removeReplacedFile): self
    {
        $this->removeReplacedFile = $removeReplacedFile;

        return $this;
    }

    public function isRemoveReplacedFileOnError(): bool
    {
        return $this->removeReplacedFileOnError;
    }

    public function setRemoveReplacedFileOnError(bool $removeReplacedFileOnError): self
    {
        $this->removeReplacedFileOnError = $removeReplacedFileOnError;

        return $this;
    }
}
