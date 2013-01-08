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
    public function testNameReturnsAnUniqueName()
    {
        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\File')
            ->disableOriginalConstructor()
            ->getMock();

        $file
            ->expects($this->any())
            ->method('guessExtension')
            ->will($this->returnValue('jpeg'));

        $entity = new DummyEntity;
        $entity->setFile($file);

        $namer = new UniqidNamer();

        $this->assertRegExp('/[a-z0-9]{13}.jpeg/', $namer->name($entity, 'file'));
    }
}
