<?php

namespace Vich\UploaderBundle\Tests\Metadata\Driver;

use Metadata\Driver\FileLocatorInterface;
use Vich\UploaderBundle\Metadata\Driver\YamlDriver;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class YamlDriverTest extends FileDriverTestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testInconsistentYamlFile()
    {
        $rClass = new \ReflectionClass(\DateTime::class);

        $fileLocator = $this->createMock(FileLocatorInterface::class);
        $fileLocator
            ->expects($this->once())
            ->method('findFileForClass')
            ->with($this->equalTo($rClass), $this->equalTo('yml'))
            ->will($this->returnValue('something not null'));

        $driver = new TestableYamlDriver($fileLocator);

        $driver->mappingContent = [];

        $driver->loadMetadataForClass($rClass);
    }

    protected function getExtension()
    {
        return 'yml';
    }

    protected function getDriver($reflectionClass, $file)
    {
        return new YamlDriver($this->getFileLocatorMock($reflectionClass, $file));
    }
}

class TestableYamlDriver extends YamlDriver
{
    public $mappingContent;

    protected function loadMappingFile($file)
    {
        return $this->mappingContent;
    }
}
