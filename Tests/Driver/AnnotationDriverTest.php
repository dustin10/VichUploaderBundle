<?php

namespace Vich\UploaderBundle\Tests\Driver;

use Vich\UploaderBundle\Driver\AnnotationDriver;
use Vich\UploaderBundle\Annotation\Uploadable;
use Vich\UploaderBundle\Annotation\UploadableField;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TwoFieldsDummyEntity;

/**
 * AnnotationDriverTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class AnnotationDriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that the driver can correctly read the Uploadable
     * annotation.
     */
    public function testReadUploadableAnnotation()
    {
        $uploadable = $this->getMockBuilder('Vich\UploaderBundle\Mapping\Annotation\Uploadable')
                      ->disableOriginalConstructor()
                      ->getMock();

        $reader = $this->getMock('Doctrine\Common\Annotations\Reader');
        $reader
            ->expects($this->once())
            ->method('getClassAnnotation')
            ->will($this->returnValue($uploadable));

        $entity = new DummyEntity();
        $driver = new AnnotationDriver($reader);
        $annot = $driver->readUploadable(new \ReflectionClass($entity));

        $this->assertEquals($uploadable, $annot);
    }

    /**
     * Tests that the driver returns null when no Uploadable annotation
     * is found.
     */
    public function testReadUploadableAnnotationReturnsNullWhenNonePresent()
    {
        $reader = $this->getMock('Doctrine\Common\Annotations\Reader');
        $reader
            ->expects($this->once())
            ->method('getClassAnnotation')
            ->will($this->returnValue(null));

        $entity = new DummyEntity();
        $driver = new AnnotationDriver($reader);
        $annot = $driver->readUploadable(new \ReflectionClass($entity));

        $this->assertEquals(null, $annot);
    }

    /**
     * Tests that the driver correctly reads one UploadableField
     * property.
     */
    public function testReadOneUploadableField()
    {
        $uploadableField = $this->getMockBuilder('Vich\UploaderBundle\Mapping\Annotation\UploadableField')
                           ->disableOriginalConstructor()
                           ->getMock();
        $uploadableField
           ->expects($this->once())
           ->method('setPropertyName');

        $entity = new DummyEntity();
        $class = new \ReflectionClass($entity);

        $reader = $this->getMock('Doctrine\Common\Annotations\Reader');
        $reader
            ->expects($this->any())
            ->method('getPropertyAnnotation')
            ->will($this->returnCallback(function() use ($uploadableField) {
                $args = func_get_args();
                if ('file' === $args[0]->getName()) {
                    return $uploadableField;
                }

                return null;
            }));

        $driver = new AnnotationDriver($reader);
        $fields = $driver->readUploadableFields($class);

        $this->assertEquals(1, count($fields));
    }

    /**
     * Test that the driver correctly reads two UploadableField
     * properties.
     */
    public function testReadTwoUploadableFields()
    {
        $fileField = $this->getMockBuilder('Vich\UploaderBundle\Mapping\Annotation\UploadableField')
                     ->disableOriginalConstructor()
                     ->getMock();
        $fileField
           ->expects($this->once())
           ->method('setPropertyName');

        $imageField = $this->getMockBuilder('Vich\UploaderBundle\Mapping\Annotation\UploadableField')
                     ->disableOriginalConstructor()
                     ->getMock();
        $imageField
           ->expects($this->once())
           ->method('setPropertyName');

        $entity = new TwoFieldsDummyEntity();
        $class = new \ReflectionClass($entity);

        $reader = $this->getMock('Doctrine\Common\Annotations\Reader');
        $reader
            ->expects($this->any())
            ->method('getPropertyAnnotation')
            ->will($this->returnCallback(function() use ($fileField, $imageField) {
                $args = func_get_args();
                if ('file' === $args[0]->getName()) {
                    return $fileField;
                } elseif ('image' === $args[0]->getName()) {
                    return $imageField;
                }

                return null;
            }));

        $driver = new AnnotationDriver($reader);
        $fields = $driver->readUploadableFields($class);

        $this->assertEquals(2, count($fields));
    }

    /**
     * Test that the driver reads zero UploadableField
     * properties when none exist.
     */
    public function testReadNoUploadableFieldsWhenNoneExist()
    {
        $entity = new DummyEntity();
        $class = new \ReflectionClass($entity);

        $reader = $this->getMock('Doctrine\Common\Annotations\Reader');
        $reader
            ->expects($this->any())
            ->method('getPropertyAnnotation')
            ->will($this->returnValue(null));

        $driver = new AnnotationDriver($reader);
        $fields = $driver->readUploadableFields($class);

        $this->assertEquals(0, count($fields));
    }
}
