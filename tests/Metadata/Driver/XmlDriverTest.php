<?php

namespace Vich\UploaderBundle\Tests\Metadata\Driver;

use Metadata\Driver\DriverInterface;
use Vich\UploaderBundle\Metadata\Driver\XmlDriver;
use Vich\TestBundle\Entity\Article;
use Vich\TestBundle\Entity\Product;

class XmlDriverTest extends FileDriverTestCase
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

    protected static function getExtension(): string
    {
        return 'xml';
    }

    protected function getDriver(\ReflectionClass $reflectionClass, ?string $file): DriverInterface
    {
        return new XmlDriver($this->getFileLocatorMock($reflectionClass, $file));
    }
}
