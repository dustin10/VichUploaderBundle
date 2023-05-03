<?php

namespace Vich\UploaderBundle\Tests\Metadata\Driver;

use Metadata\Driver\DriverInterface;
use Metadata\Driver\FileLocatorInterface;
use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Metadata\ClassMetadata;

abstract class FileDriverTestCase extends TestCase
{
    /**
     * @dataProvider classesProvider
     */
    public function testLoadMetadataForClass(string $class, string $file, array $expectedMetadata): void
    {
        $reflectionClass = new \ReflectionClass($class);
        $driver = $this->getDriver($reflectionClass, $file);
        /** @var ClassMetadata $metadata */
        $metadata = $driver->loadMetadataForClass($reflectionClass);

        self::assertInstanceOf(ClassMetadata::class, $metadata);
        self::assertIsArray($metadata->fields);
        self::assertEquals($expectedMetadata, $metadata->fields);
    }

    protected function getFileLocatorMock(\ReflectionClass $class, ?string $foundFile = null): FileLocatorInterface
    {
        $fileLocator = $this->createMock(FileLocatorInterface::class);
        $fileLocator
            ->expects(self::once())
            ->method('findFileForClass')
            ->with(self::equalTo($class), self::equalTo($this->getExtension()))
            ->willReturn($foundFile);

        return $fileLocator;
    }

    abstract static function classesProvider(): array;

    abstract static protected function getExtension(): string;

    abstract protected function getDriver(\ReflectionClass $reflectionClass, ?string $file): DriverInterface;
}
