<?php

namespace Vich\UploaderBundle\Tests\Metadata;

use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Metadata\MetadataReader;

class MetadataReaderTest extends TestCase
{
    protected $reader;
    protected $factory;

    protected function setUp()
    {
        $this->factory = $this->createMock('Metadata\AdvancedMetadataFactoryInterface');
        $this->reader = new MetadataReader($this->factory);
    }

    public function testIsUploadable()
    {
        $this->factory
            ->expects($this->once())
            ->method('getMetadataForClass')
            ->with('ClassName')
            ->will($this->returnValue('something not null'));

        $this->assertTrue($this->reader->isUploadable('ClassName'));
    }

    public function testIsUploadableWithGivenMapping()
    {
        $fields = ['field' => ['mapping' => 'joe']];
        $classMetadata = new \stdClass();
        $classMetadata->fields = $fields;
        $metadata = new \stdClass();
        $metadata->classMetadata = ['ClassName' => $classMetadata];

        $this->factory
            ->method('getMetadataForClass')
            ->with('ClassName')
            ->will($this->returnValue($metadata));

        $this->assertTrue($this->reader->isUploadable('ClassName', 'joe'));
        $this->assertFalse($this->reader->isUploadable('ClassName', 'foo'));
    }

    public function testIsUploadableForNotUploadable()
    {
        $this->factory
            ->expects($this->once())
            ->method('getMetadataForClass')
            ->with('ClassName')
            ->will($this->returnValue(null));

        $this->assertFalse($this->reader->isUploadable('ClassName'));
    }

    public function testGetUploadableClassesForwardsCallsToTheFactory()
    {
        $this->factory
            ->expects($this->once())
            ->method('getAllClassNames');

        $this->reader->getUploadableClasses();
    }

    public function testGetUploadableFields()
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
            ->will($this->returnValue($metadata));

        $this->assertSame($fields, $this->reader->getUploadableFields('ClassName'));

        $barFields = ['bar' => ['mapping' => 'bar_mapping']];
        $this->assertSame($barFields, $this->reader->getUploadableFields('ClassName', 'bar_mapping'));
    }

    public function testGetUploadableFieldsWithInheritance()
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
            ->will($this->returnValue($metadata));

        $this->assertSame(['bar', 'baz', 'foo'], $this->reader->getUploadableFields('SubClassName'));
    }

    /**
     * @dataProvider fieldsMetadataProvider
     */
    public function testGetUploadableField(array $fields, $expectedMetadata)
    {
        $classMetadata = new \stdClass();
        $classMetadata->fields = $fields;
        $metadata = new \stdClass();
        $metadata->classMetadata = ['ClassName' => $classMetadata];

        $this->factory
            ->expects($this->once())
            ->method('getMetadataForClass')
            ->with('ClassName')
            ->will($this->returnValue($metadata));

        $this->assertSame($expectedMetadata, $this->reader->getUploadableField('ClassName', 'field'));
    }

    /**
     * @expectedException \Vich\UploaderBundle\Exception\MappingNotFoundException
     * @expectedExceptionMessage Mapping not found. The configuration for the class "InvalidClassName" is probably incorrect.
     */
    public function testGetUploadableFieldWithInvalidClass()
    {
        $this->reader->getUploadableFields('InvalidClassName');
    }

    /**
     * @expectedException \Vich\UploaderBundle\Exception\MappingNotFoundException
     * @expectedExceptionMessage Mapping "foo_mapping" does not exist. The configuration for the class "InvalidClassName" is probably incorrect.
     */
    public function testGetUploadableFieldWithInvalidClassMapping()
    {
        $this->reader->getUploadableFields('InvalidClassName', 'foo_mapping');
    }

    public function fieldsMetadataProvider()
    {
        return [
            [['field' => 'toto'], 'toto'],
            [['lala' => 'toto'], null],
        ];
    }
}
