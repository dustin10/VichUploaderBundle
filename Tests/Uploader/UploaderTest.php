<?php

namespace Vich\UploaderBundle\Tests;

use Vich\UploaderBundle\Upload\Uploader;

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
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        
        $uploadable = $this->getMock('Vich\UploaderBundle\Model\UploadableInterface');
        $uploadable
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('MyFile.png'));
        
        $mappings = array(
            get_class($uploadable) => array(
                'upload_dir' => '/var/www/symfony/web/images'
            )
        );
        
        $uploader = new Uploader($container, $mappings, 'web');
        $path = $uploader->getPublicPath($uploadable);
        
        $this->assertEquals('/images/MyFile.png', $path);
    }
    
    /**
     * Test that the getPublicPath method throws an exception when no mapping 
     * is found.
     * 
     * @expectedException \InvalidArgumentException
     */
    public function testGetPublicPathThrowsException()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        
        $uploadable = $this->getMock('Vich\UploaderBundle\Model\UploadableInterface');
        
        $mappings = array(
            'Foo\BarBundle\Entity\Widget' => array(
                'upload_dir' => '/var/www/symfony/web/images'
            )
        );
        
        $uploader = new Uploader($container, $mappings, 'web');
        $path = $uploader->getPublicPath($uploadable);
    }
}
