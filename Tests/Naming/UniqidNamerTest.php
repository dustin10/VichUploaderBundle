<?php

namespace Vich\UploaderBundle\Tests\Storage;

use Vich\UploaderBundle\Tests\DummyEntity;

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
            array('jpeg', '/[a-z0-9]{13}.jpeg/'),
            array(null, '/[a-z0-9]{13}/'),
        );
    }

    /**
     * @dataProvider fileDataProvider
     */
    public function testNameReturnsAnUniqueName($extension, $pattern)
    {
        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\File')
            ->disableOriginalConstructor()
            ->getMock();

        $file
            ->expects($this->any())
            ->method('guessExtension')
            ->will($this->returnValue($extension));

        $entity = new DummyEntity;
        $entity->setFile($file);

        $namer = new UniqidNamer();

        $this->assertRegExp($pattern, $namer->name($entity, 'file'));
    }
}
