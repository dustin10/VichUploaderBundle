<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Vich\UploaderBundle\Naming\CurrentDateDirectoryNamer;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * CurrentDateDirectoryNamerTest
 *
 * @author David Romaní <david@flux.cat>
 */
class CurrentDateDirectoryNamerTest extends \PHPUnit_Framework_TestCase
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
    public function testDirectoryNameReturnsACurrentDate($originalName, $guessedExtension, $pattern)
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

        $entity = new DummyEntity;
        $entity->setFile($file);

        $directoryNamer = new CurrentDateDirectoryNamer();
        $currentDate = new \DateTime();

        $this->assertEquals(
            'uploadDir/' . $currentDate->format('Y/m/d'),
            $directoryNamer->directoryName($entity, 'file', 'uploadDir')
        );
    }
}
