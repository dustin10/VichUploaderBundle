<?php

namespace Vich\UploaderBundle\Naming\Polyfill;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;

trait FileExtensionTrait
{
    /**
     * Guess the extension of the given file.
     */
    private function getExtension(File $file): ?string
    {
        if (!$file instanceof UploadedFile && !$file instanceof ReplacingFile) {
            throw new \InvalidArgumentException('Unexpected type for $file: '.$file::class);
        }
        $originalName = $file->getClientOriginalName();

        if ('' !== ($extension = \pathinfo($originalName, \PATHINFO_EXTENSION))) {
            return $extension;
        }

        if ('' !== ($extension = $file->guessExtension())) {
            return $extension;
        }

        return null;
    }
}
