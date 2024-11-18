<?php

namespace Vich\UploaderBundle\Naming;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Util\Transliterator;

/**
 * @author Ivan Borzenkov <ivan.borzenkov@gmail.com>
 */
final class OrignameNamer implements NamerInterface, ConfigurableInterface
{
    use Polyfill\FileExtensionTrait;

    private bool $transliterate = false;

    public function __construct(private readonly Transliterator $transliterator)
    {
    }

    /**
     * @param array $options Options for this namer. The following options are accepted:
     *                       - transliterate: whether the filename should be transliterated or not
     */
    public function configure(array $options): void
    {
        $this->transliterate = isset($options['transliterate']) ? (bool) $options['transliterate'] : $this->transliterate;
    }

    public function name(object $object, PropertyMapping $mapping): string
    {
        /* @var $file UploadedFile|ReplacingFile */
        $file = $mapping->getFile($object);
        $name = $file->getClientOriginalName();

        if ($this->transliterate) {
            $name = $this->transliterator->transliterate($name);
        }

        $extension = $this->getExtension($file);

        if (\is_string($extension) && '' !== $extension && !str_ends_with($name, ".$extension")) {
            $name .= ".$extension";
        }

        return \uniqid().'_'.$name;
    }
}
