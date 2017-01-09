<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Vich\UploaderBundle\Naming\SubdirDirectoryNamer;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 */
class SubdirDirectoryNamerTest extends TestCase
{
    public function fileDataProvider()
    {
        return [
            ['0123456789.jpg', '01', 2, 1],
            ['0123456789.jpg', '01/23', 2, 2],
            ['0123456789.jpg', '012', 3, 1],
            ['0123456789.jpg', '0', 1, 1],
            ['0123456789.jpg', '0/1/2/3', 1, 4],
        ];
    }

    /**
     * @dataProvider fileDataProvider
     */
    public function testNameReturnsTheRightName($fileName, $expectedFileName, $charsPerDir, $dirs)
    {
        $entity = new DummyEntity();
        $entity->setFileName($fileName);

        $mapping = $this->getPropertyMappingMock();
        $mapping->expects($this->once())
            ->method('getFileName')
            ->with($entity)
            ->will($this->returnValue($fileName));

        $namer = new SubdirDirectoryNamer();
        $namer->configure(['chars_per_dir' => $charsPerDir, 'dirs' => $dirs]);

        $this->assertSame($expectedFileName, $namer->directoryName($entity, $mapping));
    }
}
