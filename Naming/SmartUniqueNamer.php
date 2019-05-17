<?php

namespace Vich\UploaderBundle\Naming;

use Behat\Transliterator\Transliterator;
use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * This namer makes filename unique by appending a uniqid.
 * Also, filename is made web-friendly by transliteration.
 *
 * @author Massimiliano Arione <garakkio@gmail.com>
 */
final class SmartUniqueNamer implements NamerInterface
{
    public function name($object, PropertyMapping $mapping): string
    {
        $file = $mapping->getFile($object);
        $originalName = $file->getClientOriginalName();
        $originalExtension = \pathinfo($originalName, PATHINFO_EXTENSION);
        $originalBasename = \basename($originalName, '.'.$originalExtension);
        $originalBasename = Transliterator::transliterate($originalBasename);

        return \sprintf('%s%s.%s', $originalBasename, \str_replace('.', '', \uniqid('-', true)), $originalExtension);
    }
}
