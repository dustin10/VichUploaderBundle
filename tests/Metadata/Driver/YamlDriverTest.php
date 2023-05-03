<?php

namespace Vich\UploaderBundle\Tests\Metadata\Driver;

use Metadata\Driver\DriverInterface;
use Metadata\Driver\FileLocatorInterface;
use Vich\TestBundle\Entity\Article;
use Vich\TestBundle\Entity\Product;
use Vich\UploaderBundle\Metadata\Driver\AbstractYamlDriver;
use Vich\UploaderBundle\Metadata\Driver\YamlDriver;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
final class YamlDriverTest extends FileDriverTestCase
{
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

    public function testInconsistentYamlFile(): void
    {
        $this->expectException(\RuntimeException::class);

        $rClass = new \ReflectionClass(\DateTime::class);

        $fileLocator = $this->createMock(FileLocatorInterface::class);
        $fileLocator
            ->expects(self::once())
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
