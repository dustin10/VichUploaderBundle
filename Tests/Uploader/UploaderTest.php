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

    }
    
    /**
     * Test that the getPublicPath method throws an exception when no mapping 
     * is found.
     * 
     * @expectedException \InvalidArgumentException
     */
    public function testGetPublicPathThrowsException()
    {

    }
}
