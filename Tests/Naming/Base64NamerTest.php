<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

class Base64Namer extends \Vich\UploaderBundle\Naming\Base64Namer
{
    protected function getRandomChar()
    {
        return 'a';
    }
}

/**
 * @author Keleti Márton <tejes@hac.hu>
 */
class Base64NamerTest extends TestCase
{
    public function fileDataProvider()
    {
        return [
            ['aaaaaaaaaa.jpg', 'jpg', null],
            ['aaaaaaaaaa', '', null],
            ['aaaaaaaa.jpg', 'jpg', 8],
            ['aaaaaaaaaaaaaaaa.png', 'png', 16],
        ];
    }

    /**
     * @dataProvider fileDataProvider
     */
    public function testNameReturnsTheRightName($expectedFileName, $extension, $length)
    {
        $file = $this->getUploadedFileMock();
        $file->expects($this->once())
            ->method('getClientOriginalExtension')
            ->will($this->returnValue($extension));

        $entity = new DummyEntity();
        $entity->setFile($file);

        $mapping = $this->getPropertyMappingMock();
        $mapping->expects($this->once())
            ->method('getFile')
            ->with($entity)
            ->will($this->returnValue($file));

        $namer = new Base64Namer();
        $namer->configure(['length' => $length]);

        $this->assertSame($expectedFileName, $namer->name($entity, $mapping));
    }
}
