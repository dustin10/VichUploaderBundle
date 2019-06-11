<?php

namespace Vich\UploaderBundle\Tests\Metadata;

use Metadata\AdvancedMetadataFactoryInterface;
use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Metadata\MetadataReader;

class MetadataReaderTest extends TestCase
{
    protected $reader;

    protected $factory;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(AdvancedMetadataFactoryInterface::class);
        $this->reader = new MetadataReader($this->factory);
    }

    public function testIsUploadable(): void
    {
        $this->factory
            ->expects($this->once())
            ->method('getMetadataForClass')
            ->with('ClassName')
            ->willReturn('something not null');

        $this->assertTrue($this->reader->isUploadable('ClassName'));
    }

    public function testIsUploadableWithGivenMapping(): void
    {
        $fields = ['field' => ['mapping' => 'joe']];
        $classMetadata = new \stdClass();
        $classMetadata->fields = $fields;
        $metadata = new \stdClass();
        $metadata->classMetadata = ['ClassName' => $classMetadata];

        $this->factory
            ->method('getMetadataForClass')
            ->with('ClassName')
            ->willReturn($metadata);

        $this->assertTrue($this->reader->isUploadable('ClassName', 'joe'));
        $this->assertFalse($this->reader->isUploadable('ClassName', 'foo'));
    }

    public function testIsUploadableForNotUploadable(): void
    {
        $this->factory
            ->expects($this->once())
            ->method('getMetadataForClass')
            ->with('ClassName')
            ->willReturn(null);

        $this->assertFalse($this->reader->isUploadable('ClassName'));
    }

    public function testGetUploadableClassesForwardsCallsToTheFactory(): void
    {
        $this->factory
            ->expects($this->once())
            ->method('getAllClassNames');

        $this->reader->getUploadableClasses();
    }

    public function testGetUploadableFields(): void
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
            ->expects($this->exactly(2))
            ->method('getMetadataForClass')
            ->with('ClassName')
            ->willReturn($metadata);

        $this->assertSame($fields, $this->reader->getUploadableFields('ClassName'));

        $barFields = ['bar' => ['mapping' => 'bar_mapping']];
        $this->assertSame($barFields, $this->reader->getUploadableFields('ClassName', 'bar_mapping'));
    }

    public function testGetUploadableFieldsWithInheritance(): void
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

        $this->assertSame(['bar', 'baz', 'foo'], $this->reader->getUploadableFields('SubClassName'));
    }

    /**
     * @dataProvider fieldsMetadataProvider
     */
    public function testGetUploadableField(array $fields, $expectedMetadata): void
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

        $this->assertSame($expectedMetadata, $this->reader->getUploadableField('ClassName', 'field'));
    }

    public function testGetUploadableFieldWithInvalidClass(): void
    {
        $this->expectException(\Vich\UploaderBundle\Exception\MappingNotFoundException::class);
        $this->expectExceptionMessage('Mapping not found. The configuration for the class "InvalidClassName" is probably incorrect.');

        $this->reader->getUploadableFields('InvalidClassName');
    }

    public function testGetUploadableFieldWithInvalidClassMapping(): void
    {
        $this->expectException(\Vich\UploaderBundle\Exception\MappingNotFoundException::class);
        $this->expectExceptionMessage('Mapping "foo_mapping" does not exist. The configuration for the class "InvalidClassName" is probably incorrect.');

        $this->reader->getUploadableFields('InvalidClassName', 'foo_mapping');
    }

    public function fieldsMetadataProvider(): array
    {
        return [
            [['field' => 'toto'], 'toto'],
            [['lala' => 'toto'], null],
        ];
    }
}
