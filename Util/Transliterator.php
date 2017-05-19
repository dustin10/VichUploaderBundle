<?php

namespace Vich\UploaderBundle\Util;

use Behat\Transliterator\Transliterator as BaseTransliterator;

class Transliterator extends BaseTransliterator
{
    /**
     * Transliterate a string. If string represents a filename, extension is kept.
     *
     * @param string $string
     * @param string $separator
     *
     * @return string
     */
    public static function transliterate($string, $separator = '-')
    {
        $extension = pathinfo($string, PATHINFO_EXTENSION);
        $filename = pathinfo($string, PATHINFO_FILENAME);
        $transliterated = parent::transliterate($filename, $separator);
        if (!empty($extension)) {
            $transliterated .= '.'.$extension;
        }

        return $transliterated;
    }
}
