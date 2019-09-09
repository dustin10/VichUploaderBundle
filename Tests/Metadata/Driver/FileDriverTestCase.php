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
    public function testLoadMetadataForClass($class, $file, $expectedMetadata): void
    {
        $reflectionClass = new \ReflectionClass($class);
        $driver = $this->getDriver($reflectionClass, $file);

        $metadata = $driver->loadMetadataForClass($reflectionClass);

        $this->assertInstanceOf(ClassMetadata::class, $metadata);
        $this->assertObjectHasAttribute('fields', $metadata);
        $this->assertEquals($expectedMetadata, $metadata->fields);
    }

    public function classesProvider(): array
    {
        $metadatas = [];
        $metadatas[] = [
            Product::class,
            __DIR__.'/../../Fixtures/App/src/TestBundle/Resources/config/vich_uploader/Entity.Product.'.$this->getExtension(),
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
            __DIR__.'/../../Fixtures/App/src/TestBundle/Resources/config/vich_uploader/Entity.Article.'.$this->getExtension(),
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

    protected function getFileLocatorMock(\ReflectionClass $class, $foundFile = null)
    {
        $fileLocator = $this->createMock(FileLocatorInterface::class);
        $fileLocator
            ->expects($this->once())
            ->method('findFileForClass')
            ->with($this->equalTo($class), $this->equalTo($this->getExtension()))
            ->willReturn($foundFile);

        return $fileLocator;
    }

    abstract protected function getExtension();

    /**
     * @param $reflectionClass
     * @param $file
     *
     * @return DriverInterface
     */
    abstract protected function getDriver($reflectionClass, $file);
}
