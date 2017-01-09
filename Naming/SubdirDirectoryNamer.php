<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * Directory namer wich can create subfolder depends on generated filename.
 *
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 */
class SubdirDirectoryNamer implements DirectoryNamerInterface, ConfigurableInterface
{
    private $charsPerDir = 2;
    private $dirs = 1;

    /**
     * @param array $options Options for this namer. The following options are accepted:
     *                       - chars_per_dir: how many chars use for each dir.
     *                       - dirs: how many dirs create
     */
    public function configure(array $options)
    {
        $options = array_merge(['chars_per_dir' => $this->charsPerDir, 'dirs' => $this->dirs], $options);

        $this->charsPerDir = $options['chars_per_dir'];
        $this->dirs = $options['dirs'];
    }

    /**
     * {@inheritdoc}
     */
    public function directoryName($object, PropertyMapping $mapping)
    {
        $fileName = $mapping->getFileName($object);

        $parts = [];
        for ($i = 0, $start = 0; $i < $this->dirs; $i++, $start += $this->charsPerDir) {
            $parts[] = substr($fileName, $start, $this->charsPerDir);
        }

        return implode('/', $parts);
    }
}
