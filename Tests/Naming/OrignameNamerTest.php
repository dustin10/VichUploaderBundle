<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Vich\UploaderBundle\Naming\OrignameNamer;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * OrignameNamerTest.
 *
 * @author Ivan Borzenkov <ivan.borzenkov@gmail.com>
 */
class OrignameNamerTest extends TestCase
{
    public function fileDataProvider()
    {
        return [
            ['file.jpeg', '/[a-z0-9]{13}_file.jpeg/', false],
            ['file',      '/[a-z0-9]{13}_file/',      false],
            ['Yéöù.jpeg', '/[a-z0-9]{13}_yeou.jpeg/', true],
        ];
    }

    /**
     * @dataProvider fileDataProvider
     */
    public function testNameReturnsAnUniqueName($name, $pattern, $transliterate)
    {
        $file = $this->getUploadedFileMock();
        $file
            ->expects($this->any())
            ->method('getClientOriginalName')
            ->will($this->returnValue($name));

        $entity = new \DateTime();

        $mapping = $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
            ->disableOriginalConstructor()
            ->getMock();
        $mapping->expects($this->once())
            ->method('getFile')
            ->with($entity)
            ->will($this->returnValue($file));

        $namer = new OrignameNamer();
        $namer->configure(['transliterate' => $transliterate]);

        $this->assertRegExp($pattern, $namer->name($entity, $mapping));
    }
}
