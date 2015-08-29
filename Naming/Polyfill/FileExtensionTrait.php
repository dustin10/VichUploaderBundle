<?php

namespace Vich\UploaderBundle\Naming\Polyfill;

use Symfony\Component\HttpFoundation\File\UploadedFile;

trait FileExtensionTrait
{
    /**
     * Guess the extension of the given file.
     *
     * @param UploadedFile $file
     *
     * @return string|null
     */
    private function getExtension(UploadedFile $file)
    {
        $originalName = $file->getClientOriginalName();

        if ($extension = pathinfo($originalName, PATHINFO_EXTENSION)) {
            return $extension;
        }

        if ($extension = $file->guessExtension()) {
            return $extension;
        }

        return null;
    }
}
