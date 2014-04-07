<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Vich\UploaderBundle\Naming\UniqidNamer;

/**
 * UniqidNamerTest.
 *
 * @author Emmanuel Vella <vella.emmanuel@gmail.com>
 */
class UniqidNamerTest extends \PHPUnit_Framework_TestCase
{
    public function fileDataProvider()
    {
        return array(
            //    original_name,    guessed_extension,  pattern
            array('lala.jpeg',      null,               '/[a-z0-9]{13}.jpeg/'),
            array('lala.mp3',       'mpga',             '/[a-z0-9]{13}.mp3/'),
            array('lala',           'mpga',             '/[a-z0-9]{13}.mpga/'),
            array('lala',           null,               '/[a-z0-9]{13}/'),
        );
    }

    /**
     * @dataProvider fileDataProvider
     */
    public function testNameReturnsAnUniqueName($originalName, $guessedExtension, $pattern)
    {
        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();
        $file
            ->expects($this->any())
            ->method('getClientOriginalName')
            ->will($this->returnValue($originalName));
        $file
            ->expects($this->any())
            ->method('guessExtension')
            ->will($this->returnValue($guessedExtension));

        $entity = new \DateTime();

        $mapping = $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
            ->disableOriginalConstructor()
            ->getMock();
        $mapping->expects($this->once())
            ->method('getFile')
            ->with($entity)
            ->will($this->returnValue($file));

        $namer = new UniqidNamer();

        $this->assertRegExp($pattern, $namer->name($entity, $mapping));
    }
}
