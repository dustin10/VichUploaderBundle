<?php

namespace Vich\UploaderBundle\Tests;

use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

class DummyDirectoryNamer implements DirectoryNamerInterface
{
    protected $directoryNamerResult;

    public function __construct($directoryNamerResult)
    {
        $this->directoryNamerResult = $directoryNamerResult;
    }

    /**
     * (non-PHPdoc)
     * @see Vich\UploaderBundle\Naming.DirectoryNamerInterface::directoryName()
     */
    public function directoryName($obj, $field, $uploadDir)
    {
        return $uploadDir.$this->directoryNamerResult;
    }
}
