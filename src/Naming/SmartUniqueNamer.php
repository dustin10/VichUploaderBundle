<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Util\Transliterator;

/**
 * This namer makes filename unique by appending a uniqid.
 * Also, filename is made web-friendly by transliteration.
 *
 * @author Massimiliano Arione <garakkio@gmail.com>
 */
final class SmartUniqueNamer implements NamerInterface
{
    /**
     * @var Transliterator
     */
    private $transliterator;

    public function __construct(Transliterator $transliterator)
    {
        $this->transliterator = $transliterator;
    }

    public function name($object, PropertyMapping $mapping): string
    {
        $file = $mapping->getFile($object);
        $originalName = $file->getClientOriginalName();
        $originalName = $this->transliterator->transliterate($originalName);
        $originalExtension = \strtolower(\pathinfo($originalName, \PATHINFO_EXTENSION));
        $originalBasename = \pathinfo($originalName, \PATHINFO_FILENAME);
        $uniqId = \str_replace('.', '', \uniqid('-', true));
        $uniqExtension = \sprintf('%s.%s', $uniqId, $originalExtension);
        $smartName = \sprintf('%s%s', $originalBasename, $uniqExtension);

        // Check if smartName is an acceptable size (some filesystems accept a max of 255)
        if (\strlen($smartName) <= 255) {
            return $smartName;
        }

        // Check if the unique extension will fit into 255
        // 254 to account for a one letter basename
        if (\strlen($uniqExtension) <= 254) {
            // Resize the basename to fit into 255 alongside the unique ID and extension
            $shrinkBasenameSize = 255 - \strlen($uniqExtension);
            $shortBasename = \substr($originalBasename, 0, $shrinkBasenameSize);

            return \sprintf('%s%s', $shortBasename, $uniqExtension);
        }

        // The extension is too long, but first try to preserve the basename, if possible
        // 253 is used to account for a dot and one letter extension
        $uniqBasename = \sprintf('%s%s', $originalBasename, $uniqId);
        if (\strlen($uniqBasename) <= 253) {
            // Resize the extension to fit into 255 alongside the basename, unique ID, and the dot
            // 254 is used to account for the dot
            $shrinkExtensionSize = 254 - \strlen($uniqBasename);
            $shortExtension = \substr($originalExtension, 0, $shrinkExtensionSize);

            return \sprintf('%s.%s', $uniqBasename, $shortExtension);
        }

        // Both the basename and extension are too long
        // 251 is used to account for a dot and 3 letter extension
        $shrinkBasenameSize = 251 - \strlen($uniqId);
        $shortBasename = \substr($originalBasename, 0, $shrinkBasenameSize);
        $shortExtension = \substr($originalExtension, 0, 3);

        return \sprintf('%s%s.%s', $shortBasename, $uniqId, $shortExtension);
    }
}
