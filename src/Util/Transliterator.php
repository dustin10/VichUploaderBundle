<?php

namespace Vich\UploaderBundle\Util;

use Symfony\Component\String\Slugger\SluggerInterface;
use function strrpos;
use function strtolower;
use function substr;

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
        [$filename, $extension] = $this->splitNameByExtension($string);
        $transliterated = $this->slugger->slug($filename, $separator);
        if ('' !== $extension) {
            $transliterated .= '.'.$extension;
        }

        return strtolower($transliterated);
    }

    /**
     * Splits filename for array of basename and extension.
     *
     * @return array An array of basename and extension
     */
    private function splitNameByExtension(string $filename): array
    {
        if (false === $pos = strrpos($filename, '.')) {
            return [$filename, ''];
        }

        return [substr($filename, 0, $pos), substr($filename, $pos + 1)];
    }
}
