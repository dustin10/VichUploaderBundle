<?php

namespace Vich\UploaderBundle\Tests\Upload;

use Vich\UploaderBundle\Upload\Uploader;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * UploaderTest.
 * 
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class UploaderTest extends \PHPUnit_Framework_TestCase
{   
    /**
     * Test that the getPublicPath method returns the correct path.
     */
    public function testGetPublicPathIsCorrect()
    {
        $uploadableField = $this->getMockBuilder('Vich\UploaderBundle\Annotation\UploadableField')
                           ->disableOriginalConstructor()
                           ->getMock();
        $uploadableField
            ->expects($this->once())
            ->method('getPropertyName')
            ->will($this->returnValue('file'));

        $uploadableField
            ->expects($this->once())
            ->method('getFileNameProperty')
            ->will($this->returnValue('fileName'));

        $uploadableField
            ->expects($this->once())
            ->method('getMapping')
            ->will($this->returnValue('dummy_file'));

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
                    ->disableOriginalConstructor()
                    ->getMock();

        $driver = $this->getMockBuilder('Vich\UploaderBundle\Driver\AnnotationDriver')
                  ->disableOriginalConstructor()
                  ->getMock();
        $driver
            ->expects($this->once())
            ->method('readUploadableFields')
            ->will($this->returnValue(array($uploadableField)));

        $entity = new DummyEntity();
        $entity->setFileName('MyFile.png');

        $adapter = $this->getMock('Vich\UploaderBundle\Adapter\AdapterInterface');
        $adapter
            ->expects($this->once())
            ->method('getReflectionClass')
            ->will($this->returnValue(new \ReflectionClass($entity)));

        $mappings = array(
            'dummy_file' => array(
                'upload_dir' => '/var/www/symfony/web/images'
            )
        );

        $uploader = new Uploader($container, $driver, $adapter, $mappings, 'web');
        $path = $uploader->getPublicPath($entity, 'file');

        $this->assertEquals('/images/MyFile.png', $path);
    }
    
    /**
     * Test that the getPublicPath method throws an exception when an
     * invalid mapping is specified.
     * 
     * @expectedException \InvalidArgumentException
     */
    public function testGetPublicPathThrowsExceptionOnInvalidMapping()
    {
        $uploadableField = $this->getMockBuilder('Vich\UploaderBundle\Annotation\UploadableField')
                           ->disableOriginalConstructor()
                           ->getMock();
        $uploadableField
            ->expects($this->once())
            ->method('getPropertyName')
            ->will($this->returnValue('file'));

        $uploadableField
            ->expects($this->once())
            ->method('getMapping')
            ->will($this->returnValue('dummy_file'));

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
                    ->disableOriginalConstructor()
                    ->getMock();

        $driver = $this->getMockBuilder('Vich\UploaderBundle\Driver\AnnotationDriver')
                  ->disableOriginalConstructor()
                  ->getMock();
        $driver
            ->expects($this->once())
            ->method('readUploadableFields')
            ->will($this->returnValue(array($uploadableField)));

        $entity = new DummyEntity();
        $entity->setFileName('MyFile.png');

        $adapter = $this->getMock('Vich\UploaderBundle\Adapter\AdapterInterface');
        $adapter
            ->expects($this->once())
            ->method('getReflectionClass')
            ->will($this->returnValue(new \ReflectionClass($entity)));

        $mappings = array(
            'bad_name' => array(
                'upload_dir' => '/var/www/symfony/web/images'
            )
        );

        $uploader = new Uploader($container, $driver, $adapter, $mappings, 'web');
        $path = $uploader->getPublicPath($entity, 'file');

        $this->assertEquals('/images/MyFile.png', $path);
    }
}
