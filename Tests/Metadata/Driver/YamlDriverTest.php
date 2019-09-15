<?php

namespace Vich\UploaderBundle\Tests\Metadata\Driver;

use Metadata\Driver\DriverInterface;
use Metadata\Driver\FileLocatorInterface;
use Vich\UploaderBundle\Metadata\Driver\YamlDriver;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class YamlDriverTest extends FileDriverTestCase
{
    public function testInconsistentYamlFile(): void
    {
        $this->expectException(\RuntimeException::class);

        $rClass = new \ReflectionClass(\DateTime::class);

        $fileLocator = $this->createMock(FileLocatorInterface::class);
        $fileLocator
            ->expects($this->once())
            ->method('findFileForClass')
            ->with($this->equalTo($rClass), $this->equalTo('yml'))
            ->willReturn('something not null');

        $driver = new TestableYamlDriver($fileLocator);

        $driver->mappingContent = [];

        $driver->loadMetadataForClass($rClass);
    }

    protected function getExtension(): string
    {
        return 'yml';
    }

    protected function getDriver(\ReflectionClass $reflectionClass, ?string $file): DriverInterface
    {
        return new YamlDriver($this->getFileLocatorMock($reflectionClass, $file));
    }
}

class TestableYamlDriver extends YamlDriver
{
    public $mappingContent;

    protected function loadMappingFile(string $file)
    {
        return $this->mappingContent;
    }
}
