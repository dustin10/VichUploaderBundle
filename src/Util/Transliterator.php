<?php

namespace Vich\UploaderBundle\Util;

use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @internal
 */
class Transliterator
{
    /**
     * @var SluggerInterface
     */
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    /**
     * Transliterate a string. If string represents a filename, extension is kept.
     */
    public function transliterate(string $string, string $separator = '-'): string
    {
        [$filename, $extension] = FilenameUtils::spitNameByExtension($string);
        $transliterated = $this->slugger->slug($filename, $separator);
        if ('' !== $extension) {
            $transliterated .= '.'.$extension;
        }

        return \strtolower($transliterated);
    }
}
