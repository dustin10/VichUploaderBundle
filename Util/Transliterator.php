<?php

namespace Vich\UploaderBundle\Util;

class Transliterator
{
    public static function transliterate($string)
    {
        // needs intl extension
        $transId = "Any-Latin; Latin-ASCII; [\u0100-\u7fff] remove";
        if (class_exists('\\Transliterator') && $transliterator = \Transliterator::create($transId)) {
            $string = $transliterator->transliterate($string);
            $string = preg_replace('/[^\\pL\d._]+/u', '-', $string);
            $string = preg_replace('/[-\s]+/', '-', $string);
        } else {
            // uses iconv
            $string = preg_replace('~[^\\pL0-9_\.]+~u', '-', $string); // substitutes anything but letters, numbers and '-' with separator
            $string = trim($string, '-');
            if (function_exists('iconv')) {
                $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string); // TRANSLIT does the whole job
            }
            $string = preg_replace('~[^-a-zA-Z0-9_\.]+~', '', $string); // keep only letters, numbers, '_' and separator
        }

        $string = trim($string, '-');

        return $string;
    }
}
