<?php

namespace Vich\UploaderBundle\Tests\Metadata\Driver;

use Metadata\Driver\DriverInterface;
use Metadata\Driver\FileLocatorInterface;
use PHPUnit\Framework\TestCase;
use Vich\TestBundle\Entity\Article;
use Vich\TestBundle\Entity\Product;
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
        self::assertObjectHasAttribute('fields', $metadata);
        self::assertEquals($expectedMetadata, $metadata->fields);
    }

    public static function classesProvider(): array
    {
        $metadatas = [];
        $metadatas[] = [
            Product::class,
            __DIR__ . '/../../Fixtures/TestBundle/config/vich_uploader/Entity.Product.' . self::getExtension(),
            [
                'image' => [
                    'mapping' => 'product_image',
                    'propertyName' => 'image',
                    'fileNameProperty' => 'imageName',
                    'size' => 'imageSize',
                    'mimeType' => 'imageMimeType',
                    'originalName' => 'imageOriginalName',
                    'dimensions' => null,
                ],
            ],
        ];

        $metadatas[] = [
            Article::class,
            __DIR__ . '/../../Fixtures/TestBundle/config/vich_uploader/Entity.Article.' . self::getExtension(),
            [
                'attachment' => [
                    'mapping' => 'dummy_file',
                    'propertyName' => 'attachment',
                    'fileNameProperty' => 'attachmentName',
                    'size' => null,
                    'mimeType' => null,
                    'originalName' => null,
                    'dimensions' => null,
                ],
                'image' => [
                    'mapping' => 'dummy_image',
                    'propertyName' => 'image',
                    'fileNameProperty' => 'imageName',
                    'size' => 'imageSize',
                    'mimeType' => 'imageMimeType',
                    'originalName' => 'imageOriginalName',
                    'dimensions' => null,
                ],
            ],
        ];

        return $metadatas;
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

    abstract static protected function getExtension(): string;

    abstract protected function getDriver(\ReflectionClass $reflectionClass, ?string $file): DriverInterface;
}