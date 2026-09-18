<?php

namespace Vich\UploaderBundle\Tests\Metadata\Driver;

use Metadata\Driver\DriverInterface;
use Metadata\Driver\FileLocatorInterface;
use PHPUnit\Framework\Attributes\Test;
use Vich\UploaderBundle\Metadata\Driver\AbstractYamlDriver;
use Vich\UploaderBundle\Metadata\Driver\YamlDriver;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
final class YamlDriverTest extends FileDriverTestCase
{
    #[Test]
    public function inconsistentYamlFile(): void
    {
        $this->expectException(\RuntimeException::class);

        $rClass = new \ReflectionClass(\DateTime::class);

        $fileLocator = $this->createMock(FileLocatorInterface::class);
        $fileLocator
            ->expects($this->once())
            ->method('findFileForClass')
            ->with(self::equalTo($rClass), self::equalTo('yaml'))
            ->willReturn('something not null');

        $driver = new TestableYamlDriver($fileLocator);

        $driver->mappingContent = [];

        $driver->loadMetadataForClass($rClass);
    }

    protected static function getExtension(): string
    {
        return 'yaml';
    }

    protected function getDriver(\ReflectionClass $reflectionClass, ?string $file): DriverInterface
    {
        return new YamlDriver($this->getFileLocatorMock($reflectionClass, $file));
    }
}

final class TestableYamlDriver extends AbstractYamlDriver
{
    public array $mappingContent = [];

    protected function loadMappingFile(string $file): array
    {
        return $this->mappingContent;
    }

    protected function getExtension(): string
    {
        return 'yaml';
    }
}
