<?php

namespace Vich\UploaderBundle\Naming\Polyfill;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;

trait FileExtensionTrait
{
    // extensions safe to keep
    private static array $keep = [
        'txt' => ['csv', 'srt', 'vtt'],
        'xml' => ['gpx', 'kml'],
        'xlsx' => ['xlsb'],
    ];

    /**
     * Guess the extension of the given file.
     */
    private function getExtension(File $file): ?string
    {
        return $this->getExtensionWithOption($file, false);
    }

    /**
     * Gets the original extension from the file name without any guessing.
     */
    private function getOriginalExtension(File $file): ?string
    {
        if (!$file instanceof UploadedFile && !$file instanceof ReplacingFile) {
            throw new \InvalidArgumentException('Unexpected type for $file: '.$file::class);
        }

        $originalExtension = \pathinfo($file->getClientOriginalName(), \PATHINFO_EXTENSION);

        return '' !== $originalExtension ? $originalExtension : null;
    }

    /**
     * Gets the extension with option to keep original or use smart logic.
     */
    private function getExtensionWithOption(File $file, bool $keepOriginal): ?string
    {
        if (!$file instanceof UploadedFile && !$file instanceof ReplacingFile) {
            throw new \InvalidArgumentException('Unexpected type for $file: '.$file::class);
        }

        if ($keepOriginal) {
            return $this->getOriginalExtension($file);
        }

        if ('' !== ($extension = $file->guessExtension())) {
            if (isset(self::$keep[$extension])) {
                $originalExtension = \pathinfo($file->getClientOriginalName(), \PATHINFO_EXTENSION);
                if (\in_array($originalExtension, self::$keep[$extension], true)) {
                    return $originalExtension;
                }
            }

            return $extension;
        }

        return null;
    }
}
