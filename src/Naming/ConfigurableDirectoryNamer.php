<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * Directory namer which can create subfolder which path is given in the directory namer's options.
 */
class ConfigurableDirectoryNamer implements DirectoryNamerInterface, ConfigurableInterface
{
    /** @var string */
    private $directoryPath = '';

    /**
     * @param array $options Options for this namer. The following options are accepted:
     *                       - directory_path: the path of the folders to create
     */
    public function configure(array $options): void
    {
        if (!isset($options['directory_path'])) {
            throw new \InvalidArgumentException('Option "directory_path" is missing.');
        }

        $this->directoryPath = $options['directory_path'];
    }

    public function directoryName($object, PropertyMapping $mapping): string
    {
        return $this->directoryPath;
    }
}
