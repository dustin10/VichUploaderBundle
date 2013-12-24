<?php

namespace Vich\UploaderBundle\Metadata\Driver;

use Metadata\Driver\AdvancedFileLocatorInterface;
use Symfony\Component\Finder\Finder;

class FileLocator implements AdvancedFileLocatorInterface
{
    private $dirs;

    public function __construct(array $dirs)
    {
        $this->dirs = $dirs;
    }

    public function getDirs()
    {
        return $this->dirs;
    }

    /**
     * @param \ReflectionClass $class
     * @param string           $extension
     *
     * @return string|null
     */
    public function findFileForClass(\ReflectionClass $class, $extension)
    {
        $finder = new Finder();

        foreach ($this->dirs as $prefix => $dir) {
            if ('' !== $prefix && 0 !== strpos($class->getNamespaceName(), $prefix)) {
                continue;
            }

            $files = $finder->files()->in($dir)->name(sprintf('*%s*.%s', $class->getShortName(), $extension));

            if (count($files) !== 1) {
                continue;
            }

            $file = current(iterator_to_array($files));

            return $file->getRealpath();
        }

        return null;
    }

    /**
     * Finds all possible metadata files.
     *
     * @param string $extension
     *
     * @return array
     */
    public function findAllClasses($extension)
    {
        $files = array();
        $finder = new Finder();

        foreach ($this->dirs as $dir) {
            $results = $finder->files()->in($dir)->name('*.' . $extension);
            $files = array_merge(iterator_to_array($results), $files);
        }

        return array_unique($files);
    }
}
