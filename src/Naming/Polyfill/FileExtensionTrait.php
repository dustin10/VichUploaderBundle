<?php

namespace Vich\UploaderBundle\Naming\Polyfill;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;

trait FileExtensionTrait
{
    // extensions safe to keep
    private static array $keep = [
        'txt' => 'csv',
        'xml' => 'gpx',
    ];

    /**
     * Guess the extension of the given file.
     */
    private function getExtension(File $file): ?string
    {
        if (!$file instanceof UploadedFile && !$file instanceof ReplacingFile) {
            throw new \InvalidArgumentException('Unexpected type for $file: '.$file::class);
        }

        if ('' !== ($extension = $file->guessExtension())) {
            if (isset(self::$keep[$extension])) {
                $originalExtension = \pathinfo($file->getClientOriginalName(), \PATHINFO_EXTENSION);
                if (self::$keep[$extension] === $originalExtension) {
                    return $originalExtension;
                }
            }

            return $extension;
        }

        return null;
    }
}
