<?php

namespace Vich\UploaderBundle\Metadata\Driver;

use Metadata\Driver\FileLocatorInterface;
use Symfony\Component\Finder\Finder;

class FileLocator implements FileLocatorInterface
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
        foreach ($this->dirs as $prefix => $dir) {
            if ('' !== $prefix && 0 !== strpos($class->getNamespaceName(), $prefix)) {
                continue;
            }

            $finder = new Finder();
            $files = $finder->files()->in($dir)->name(sprintf('*%s*.%s', $class->getShortName(), $extension));

            if (count($files) !== 1) {
                continue;
            }

            $file = current(iterator_to_array($files));

            return $file->getRealpath();
        }

        return null;
    }
}
