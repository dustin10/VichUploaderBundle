<?php

namespace Vich\UploaderBundle\Util;

use Symfony\Component\String\Slugger\SluggerInterface;
use function strtolower;

/**
 * @internal
 */
final class Transliterator
{
    public function __construct(private readonly SluggerInterface $slugger)
    {
    }

    /**
     * Transliterate a string. If string represents a filename, extension is kept.
     */
    public function transliterate(string $string, string $separator = '-'): string
    {
        [$filename, $extension] = FilenameUtils::splitNameByExtension($string);
        $transliterated = $this->slugger->slug($filename, $separator);
        if ('' !== $extension) {
            $transliterated .= '.'.$extension;
        }

        return strtolower($transliterated);
    }
}
