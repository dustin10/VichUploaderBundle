<?php

namespace Vich\UploaderBundle\Tests\Fixtures;

use Symfony\Component\HttpFoundation\File\File;

final class PromotedFileEntity
{
    public function __construct(private ?File $file = null)
    {
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): void
    {
        $this->file = $file;
    }
}
