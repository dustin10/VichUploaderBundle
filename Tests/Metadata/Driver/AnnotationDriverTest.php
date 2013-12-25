<?php

namespace Vich\UploaderBundle\Tests\Metadata\Driver;

use Vich\UploaderBundle\Mapping\Annotation\UploadableField;
use Vich\UploaderBundle\Metadata\Driver\AnnotationDriver;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TwoFieldsDummyEntity;

/**
 * AnnotationDriverTest
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class AnnotationDriverTest extends \PHPUnit_Framework_TestCase
{
    public function testReadUploadableAnnotation()
    {
        $entity = new DummyEntity();

        $reader = $this->getMock('Doctrine\Common\Annotations\Reader');
        $reader
            ->expects($this->once())
            ->method('getClassAnnotation')
            ->will($this->returnValue('something not null'));
        $reader
            ->expects($this->at(1))
            ->method('getPropertyAnnotation')
            ->will($this->returnValue(new UploadableField(array(
                'mapping'           => 'dummy_file',
                'fileNameProperty'  => 'fileName'
            ))));

        $driver = new AnnotationDriver($reader);
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        $this->assertInstanceOf('\Vich\UploaderBundle\Metadata\ClassMetadata', $metadata);
        $this->assertObjectHasAttribute('fields', $metadata);
        $this->assertEquals(array(
            'file' => array(
                'mapping'           => 'dummy_file',
                'propertyName'      => null,
                'fileNameProperty'  => 'fileName',
            )
        ), $metadata->fields);
    }

    public function testReadUploadableAnnotationReturnsNullWhenNonePresent()
    {
        $entity = new DummyEntity();

        $reader = $this->getMock('Doctrine\Common\Annotations\Reader');
        $reader
            ->expects($this->once())
            ->method('getClassAnnotation')
            ->will($this->returnValue(null));
        $reader
            ->expects($this->never())
            ->method('getPropertyAnnotation');

        $driver = new AnnotationDriver($reader);
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        $this->assertNull($metadata);
    }

    public function testReadTwoUploadableFields()
    {
        $entity = new TwoFieldsDummyEntity();

        $reader = $this->getMock('Doctrine\Common\Annotations\Reader');
        $reader
            ->expects($this->once())
            ->method('getClassAnnotation')
            ->will($this->returnValue('something not null'));
        $reader
            ->expects($this->at(1))
            ->method('getPropertyAnnotation')
            ->will($this->returnValue(new UploadableField(array(
                'mapping'           => 'dummy_file',
                'fileNameProperty'  => 'fileName'
            ))));
        $reader
            ->expects($this->at(3))
            ->method('getPropertyAnnotation')
            ->will($this->returnValue(new UploadableField(array(
                'mapping'           => 'dummy_image',
                'fileNameProperty'  => 'imageName'
            ))));

        $driver = new AnnotationDriver($reader);
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        $this->assertEquals(array(
            'file' => array(
                'mapping'           => 'dummy_file',
                'propertyName'      => null,
                'fileNameProperty'  => 'fileName',
            ),
            'image' => array(
                'mapping'           => 'dummy_image',
                'propertyName'      => null,
                'fileNameProperty'  => 'imageName',
            )
        ), $metadata->fields);
    }

    public function testReadNoUploadableFieldsWhenNoneExist()
    {
        $entity = new DummyEntity();

        $reader = $this->getMock('Doctrine\Common\Annotations\Reader');
        $reader
            ->expects($this->once())
            ->method('getClassAnnotation')
            ->will($this->returnValue('something not null'));

        $driver = new AnnotationDriver($reader);
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        $this->assertEmpty($metadata->fields);
    }
}
