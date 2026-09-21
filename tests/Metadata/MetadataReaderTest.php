<?php

namespace Vich\UploaderBundle\Tests\Metadata;

use Metadata\AdvancedMetadataFactoryInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Metadata\MetadataReader;

#[AllowMockObjectsWithoutExpectations]
final class MetadataReaderTest extends TestCase
{
    protected MetadataReader $reader;

    protected MockObject|AdvancedMetadataFactoryInterface $factory;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(AdvancedMetadataFactoryInterface::class);
        $this->reader = new MetadataReader($this->factory);
    }

    #[Test]
    public function isUploadable(): void
    {
        $this->factory
            ->expects($this->once())
            ->method('getMetadataForClass')
            ->with('ClassName')
            ->willReturn('something not null');

        self::assertTrue($this->reader->isUploadable('ClassName'));
    }

    #[Test]
    public function isUploadableWithGivenMapping(): void
    {
        $fields = ['field' => ['mapping' => 'joe']];
        $classMetadata = new \stdClass();
        $classMetadata->fields = $fields;
        $metadata = new \stdClass();
        $metadata->classMetadata = ['ClassName' => $classMetadata];

        $this->factory
            ->expects($this->atLeastOnce())
            ->method('getMetadataForClass')
            ->with('ClassName')
            ->willReturn($metadata);

        self::assertTrue($this->reader->isUploadable('ClassName', 'joe'));
        self::assertFalse($this->reader->isUploadable('ClassName', 'foo'));
    }

    #[Test]
    public function isUploadableForNotUploadable(): void
    {
        $this->factory
            ->expects($this->once())
            ->method('getMetadataForClass')
            ->with('ClassName')
            ->willReturn(null);

        self::assertFalse($this->reader->isUploadable('ClassName'));
    }

    #[Test]
    public function getUploadableClassesForwardsCallsToTheFactory(): void
    {
        $this->factory
            ->expects($this->once())
            ->method('getAllClassNames');

        $this->reader->getUploadableClasses();
    }

    #[Test]
    public function getUploadableFields(): void
    {
        $fields = [
            'foo' => ['mapping' => 'foo_mapping'],
            'bar' => ['mapping' => 'bar_mapping'],
            'baz' => ['mapping' => 'baz_mapping'],
        ];
        $classMetadata = new \stdClass();
        $classMetadata->fields = $fields;
        $metadata = new \stdClass();
        $metadata->classMetadata = ['ClassName' => $classMetadata];

        $this->factory
            ->expects(self::exactly(2))
            ->method('getMetadataForClass')
            ->with('ClassName')
            ->willReturn($metadata);

        self::assertSame($fields, $this->reader->getUploadableFields('ClassName'));

        $barFields = ['bar' => ['mapping' => 'bar_mapping']];
        self::assertSame($barFields, $this->reader->getUploadableFields('ClassName', 'bar_mapping'));
    }

    #[Test]
    public function getUploadableFieldsWithInheritance(): void
    {
        $classMetadata = new \stdClass();
        $classMetadata->fields = ['bar', 'baz'];
        $subClassMetadata = new \stdClass();
        $subClassMetadata->fields = ['foo'];
        $metadata = new \stdClass();
        $metadata->classMetadata = [
            'ClassName' => $classMetadata,
            'SubClassName' => $subClassMetadata,
        ];

        $this->factory
            ->expects($this->once())
            ->method('getMetadataForClass')
            ->with('SubClassName')
            ->willReturn($metadata);

        self::assertSame(['bar', 'baz', 'foo'], $this->reader->getUploadableFields('SubClassName'));
    }

    #[DataProvider('fieldsMetadataProvider')]
    #[Test]
    public function getUploadableField(array $fields, ?string $expectedMetadata): void
    {
        $classMetadata = new \stdClass();
        $classMetadata->fields = $fields;
        $metadata = new \stdClass();
        $metadata->classMetadata = ['ClassName' => $classMetadata];

        $this->factory
            ->expects($this->once())
            ->method('getMetadataForClass')
            ->with('ClassName')
            ->willReturn($metadata);

        self::assertSame($expectedMetadata, $this->reader->getUploadableField('ClassName', 'field'));
    }

    #[Test]
    public function getUploadableFieldWithInvalidClass(): void
    {
        $this->expectException(\Vich\UploaderBundle\Exception\MappingNotFoundException::class);
        $this->expectExceptionMessage('Mapping not found. The configuration for the class "InvalidClassName" is probably incorrect.');

        $this->reader->getUploadableFields('InvalidClassName');
    }

    #[Test]
    public function getUploadableFieldWithInvalidClassMapping(): void
    {
        $this->expectException(\Vich\UploaderBundle\Exception\MappingNotFoundException::class);
        $this->expectExceptionMessage('Mapping "foo_mapping" does not exist. The configuration for the class "InvalidClassName" is probably incorrect.');

        $this->reader->getUploadableFields('InvalidClassName', 'foo_mapping');
    }

    public static function fieldsMetadataProvider(): array
    {
        return [
            [['field' => 'toto'], 'toto'],
            [['lala' => 'toto'], null],
        ];
    }
}
