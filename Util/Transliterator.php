<?php

namespace Vich\UploaderBundle\Util;

use Behat\Transliterator\Transliterator as BehatTransliterator;

class Transliterator
{
    /**
     * This class should not be instantiated.
     */
    private function __construct()
    {
    }

    /**
     * Transliterate a string. If string represents a filename, extension is kept.
     *
     * @param string|object $string
     * @param string $separator
     *
     * @return string
     */
    public static function transliterate($string, string $separator = '-'): string
    {
        if (is_object($string) && method_exists($string, 'getId')) {
            $string = $string->getId();
        } else if (is_object($string)) {
            throw new \TypeError("Argument 1 passed to Vich\UploaderBundle\Util\Transliterator::transliterate() must be of the type string or an object with the method getId()");
        }
        [$filename, $extension] = FilenameUtils::spitNameByExtension($string);

        $transliterated = BehatTransliterator::transliterate($filename, $separator);
        if ('' !== $extension) {
            $transliterated .= '.'.$extension;
        }

        return $transliterated;
    }
}
