<?php

namespace Vich\UploaderBundle\Tests\Storage;

use Vich\UploaderBundle\Naming\UniqidNamer;
use Vich\UploaderBundle\Tests\DummyEntity;

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
            //    real_extension,   guessed_extension,  pattern
            array('jpeg',           null,               '/[a-z0-9]{13}.jpeg/'),
            array('mp3',            'mpga',             '/[a-z0-9]{13}.mp3/'),
            array(null,             'mpga',             '/[a-z0-9]{13}.mpga/'),
            array(null,             null,               '/[a-z0-9]{13}/'),
        );
    }

    /**
     * @dataProvider fileDataProvider
     */
    public function testNameReturnsAnUniqueName($realExtension, $guessedExtension, $pattern)
    {
        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\File')
            ->disableOriginalConstructor()
            ->getMock();

        $file
            ->expects($this->any())
            ->method('getExtension')
            ->will($this->returnValue($realExtension));

        $file
            ->expects($this->any())
            ->method('guessExtension')
            ->will($this->returnValue($guessedExtension));

        $entity = new DummyEntity;
        $entity->setFile($file);

        $namer = new UniqidNamer();

        $this->assertRegExp($pattern, $namer->name($entity, 'file'));
    }
}
