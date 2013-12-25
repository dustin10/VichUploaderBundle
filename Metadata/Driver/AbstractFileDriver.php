<?php

namespace Vich\UploaderBundle\Metadata\Driver;

use Metadata\Driver\AdvancedDriverInterface;
use Metadata\Driver\AdvancedFileLocatorInterface;
use Metadata\Driver\FileLocatorInterface;

/**
 * Base file driver implementation.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
abstract class AbstractFileDriver implements AdvancedDriverInterface
{
    /**
     * @var FileLocatorInterface
     */
    protected $locator;

    public function __construct(FileLocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    public function loadMetadataForClass(\ReflectionClass $class)
    {
        if (null === $path = $this->locator->findFileForClass($class, $this->getExtension())) {
            return null;
        }

        return $this->loadMetadataFromFile($path, $class);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllClassNames()
    {
        if (!$this->locator instanceof AdvancedFileLocatorInterface) {
            throw new \RuntimeException('Locator "%s" must be an instance of "AdvancedFileLocatorInterface".');
        }

        $classNames = array();
        foreach ($this->locator->findAllClasses($this->getExtension()) as $file) {
            $metadata = $this->loadMetadataFromFile($file->getRealpath());
            $classNames[] = $metadata->name;
        }

        return $classNames;
    }

    /**
     * Parses the content of the file, and converts it to the desired metadata.
     *
     * @param string           $file
     * @param \ReflectionClass $class
     *
     * @return \MetaData\ClassMetadata|null
     */
    abstract protected function loadMetadataFromFile($file, \ReflectionClass $class = null);

    /**
     * Returns the extension of the file.
     *
     * @return string
     */
    abstract protected function getExtension();
}
