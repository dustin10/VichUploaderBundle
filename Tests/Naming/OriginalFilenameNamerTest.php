<?php

namespace Vich\UploaderBundle\Tests;

use Vich\UploaderBundle\Naming\OriginalFilenameNamer;

/**
 * OriginalFilenameNamerTest.
 * 
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class OriginalFilenameNamerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that the name method returns the correct name.
     */
    public function testNameIsCorrect()
    {
        $uploadedFile = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
                             ->disableOriginalConstructor()
                             ->getMock();
        $uploadedFile
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->will($this->returnValue('MyFile.png'));
        
        $uploadable = $this->getMock('Vich\UploaderBundle\Model\UploadableInterface');
        $uploadable
            ->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue($uploadedFile));
        
        $namer = new OriginalFilenameNamer();
        $name = $namer->name($uploadable);
        
        $this->assertEquals('MyFile.png', $name);
    }
}
