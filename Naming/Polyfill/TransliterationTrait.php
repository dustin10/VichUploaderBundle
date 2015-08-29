<?php

namespace Vich\UploaderBundle\Naming\Polyfill;

use Symfony\Component\HttpFoundation\File\UploadedFile;

trait TransliterationTrait
{
    private function transliterate($string)
    {
        // needs intl extension
        if (function_exists('transliterator_transliterate')) {
            $string = transliterator_transliterate("Any-Latin; Latin-ASCII; [\u0100-\u7fff] remove" , $string);
            $string = preg_replace('/[^\\pL\d.]+/u', '-', $string);
            $string = preg_replace('/[-\s]+/', '-', $string);
        } else {
            // uses iconv
            $string = preg_replace('~[^\\pL0-9_]+~u', '-', $string); // substitutes anything but letters, numbers and '-' with separator
            $string = trim($string, '-');
            if (function_exists('iconv')) {
                $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string); // TRANSLIT does the whole job
            }
            $string = strtolower($string);
            $string = preg_replace('~[^-a-z0-9_]+~', '', $string); // keep only letters, numbers, '_' and separator
        }

        $string = trim($string, '-');

        return $string;
    }
}
