<?php

namespace Vich\UploaderBundle\Tests\Storage;

use Vich\UploaderBundle\Tests\DummyEntity;

use Vich\UploaderBundle\Naming\OrignameNamer;

/**
 * OrignameNamerTest.
 *
 * @author Ivan Borzenkov <ivan.borzenkov@gmail.com>
 */
class OrignameNamerTest extends \PHPUnit_Framework_TestCase
{
    public function fileDataProvider()
    {
        return array(
            array('file.jpeg', '/[a-z0-9]{13}_file.jpeg/'),
            array('file', '/[a-z0-9]{13}_file/'),
        );
    }

    /**
     * @dataProvider fileDataProvider
     */
    public function testNameReturnsAnUniqueName($name, $pattern)
    {
        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $file
            ->expects($this->any())
            ->method('getClientOriginalName')
            ->will($this->returnValue($name));

        $entity = new DummyEntity;
        $entity->setFile($file);

        $namer = new OrignameNamer();

        $this->assertRegExp($pattern, $namer->name($entity, 'file'));
    }
}
