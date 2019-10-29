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
        $originalExtension = \strtolower(\pathinfo($originalName, PATHINFO_EXTENSION));
        $originalBasename = \basename($originalName, '.'.$originalExtension);
        $originalBasename = Transliterator::transliterate($originalBasename);
        $uniqId = \str_replace('.', '', \uniqid('-', true));
        $uniqExtension = \sprintf('%s.%s', $uniqId, $originalExtension);
        $smartName = \sprintf('%s%s', $originalBasename, $uniqExtension);
        // Check if smartName is an acceptable size (some filesystems accept a max of 255)
        if (\strlen($smartName) <= 255) {
            return $smartName;
        }

        // Shorten the basename to fit into 255 (excluding the unique extension)
        $diffSize = (255 - \strlen($uniqExtension)) - \strlen($originalBasename);
        if ($diffSize > 0) {
            $shortBasename = \substr($originalBasename, 0, $diffSize);

            return \sprintf('%s%s', $shortBasename, $uniqExtension);
        }
        // Last resort
        return $uniqExtension;
    }
}
