<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMappingInterface;

/**
 * Directory namer that chains multiple directory namers together.
 *
 * @author Guillaume Sainthillier <guillaume@silarhi.fr>
 */
final class ChainDirectoryNamer implements DirectoryNamerInterface, ConfigurableInterface
{
    /** @var array<DirectoryNamerInterface> */
    private array $namers = [];

    private string $separator = '/';

    /**
     * @param array<DirectoryNamerInterface> $namers
     */
    public function setNamers(array $namers): void
    {
        $this->namers = $namers;
    }

    /**
     * @param array $options Options for this namer. The following options are accepted:
     *                       - separator: the separator between directory names (default: '/')
     */
    public function configure(array $options): void
    {
        if (isset($options['separator'])) {
            $this->separator = (string) $options['separator'];
        }
    }

    public function directoryName(object|array $object, PropertyMappingInterface $mapping): string
    {
        $directories = [];
        foreach ($this->namers as $namer) {
            $directory = $namer->directoryName($object, $mapping);
            if ('' !== $directory) {
                $directories[] = $directory;
            }
        }

        return \implode($this->separator, $directories);
    }
}
