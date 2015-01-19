<?php

namespace Vich\UploaderBundle\Tests\Metadata;

use Vich\UploaderBundle\Metadata\MetadataReader;

class MetadataReaderTest extends \PHPUnit_Framework_TestCase
{
    protected $reader;
    protected $factory;

    public function setUp()
    {
        $this->factory = $this->getMock('Metadata\AdvancedMetadataFactoryInterface');
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
        $fields = array('field' => array('mapping' => 'joe'));
        $classMetadata = new \stdClass;
        $classMetadata->fields = $fields;
        $metadata = new \stdClass;
        $metadata->classMetadata = array('ClassName' => $classMetadata);

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
        $fields = array('foo', 'bar', 'baz');
        $classMetadata = new \stdClass;
        $classMetadata->fields = $fields;
        $metadata = new \stdClass;
        $metadata->classMetadata = array('ClassName' => $classMetadata);

        $this->factory
            ->expects($this->once())
            ->method('getMetadataForClass')
            ->with('ClassName')
            ->will($this->returnValue($metadata));

        $this->assertSame($fields, $this->reader->getUploadableFields('ClassName'));
    }

    public function testGetUploadableFieldsWithInheritance()
    {
        $classMetadata = new \stdClass;
        $classMetadata->fields = array('bar', 'baz');
        $subClassMetadata = new \stdClass;
        $subClassMetadata->fields = array('foo');
        $metadata = new \stdClass;
        $metadata->classMetadata = array(
            'ClassName'     => $classMetadata,
            'SubClassName'  => $subClassMetadata
        );

        $this->factory
            ->expects($this->once())
            ->method('getMetadataForClass')
            ->with('SubClassName')
            ->will($this->returnValue($metadata));

        $this->assertSame(array('bar', 'baz', 'foo'), $this->reader->getUploadableFields('SubClassName'));
    }

    /**
     * @dataProvider fieldsMetadataProvider
     */
    public function testGetUploadableField(array $fields, $expectedMetadata)
    {
        $classMetadata = new \stdClass;
        $classMetadata->fields = $fields;
        $metadata = new \stdClass;
        $metadata->classMetadata = array('ClassName' => $classMetadata);

        $this->factory
            ->expects($this->once())
            ->method('getMetadataForClass')
            ->with('ClassName')
            ->will($this->returnValue($metadata));

        $this->assertSame($expectedMetadata, $this->reader->getUploadableField('ClassName', 'field'));
    }

    public function fieldsMetadataProvider()
    {
        return array(
            array( array('field' => 'toto'), 'toto' ),
            array( array('lala' => 'toto'), null ),
        );
    }
}
