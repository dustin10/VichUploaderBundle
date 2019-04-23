<?php

namespace Vich\UploaderBundle\Naming;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Util\Transliterator;

/**
 * OrignameNamer.
 *
 * @author Ivan Borzenkov <ivan.borzenkov@gmail.com>
 */
class OrignameNamer implements NamerInterface, ConfigurableInterface
{
    /**
     * @var bool
     */
    private $transliterate = false;

    /**
     * @param array $options Options for this namer. The following options are accepted:
     *                       - transliterate: whether the filename should be transliterated or not
     */
    public function configure(array $options): void
    {
        $this->transliterate = isset($options['transliterate']) ? (bool) $options['transliterate'] : $this->transliterate;
    }

    /**
     * {@inheritdoc}
     */
    public function name($object, PropertyMapping $mapping): string
    {
        /* @var $file UploadedFile */
        $file = $mapping->getFile($object);
        $name = $file->getClientOriginalName();

        if ($this->transliterate) {
            $name = Transliterator::transliterate($name);
        }

        return \uniqid().'_'.$name;
    }
}
