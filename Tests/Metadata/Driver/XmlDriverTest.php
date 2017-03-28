<?php

namespace Vich\UploaderBundle\Tests\Metadata\Driver;

use Vich\UploaderBundle\Metadata\Driver\XmlDriver;

class XmlDriverTest extends FileDriverTestCase
{
    protected function getExtension()
    {
        return 'xml';
    }

    protected function getDriver($reflectionClass, $file)
    {
        return new XmlDriver($this->getFileLocatorMock($reflectionClass, $file));
    }
}
