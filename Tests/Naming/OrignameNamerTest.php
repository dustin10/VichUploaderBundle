<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\OrignameNamer;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * OrignameNamerTest.
 *
 * @author Ivan Borzenkov <ivan.borzenkov@gmail.com>
 */
class OrignameNamerTest extends TestCase
{
    public function fileDataProvider(): array
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
    public function testNameReturnsAnUniqueName($name, $pattern, $transliterate): void
    {
        $file = $this->getUploadedFileMock();
        $file
            ->expects($this->any())
            ->method('getClientOriginalName')
            ->willReturn($name);

        $entity = new \DateTime();

        $mapping = $this->getMockBuilder(PropertyMapping::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mapping->expects($this->once())
            ->method('getFile')
            ->with($entity)
            ->willReturn($file);

        $namer = new OrignameNamer();
        $namer->configure(['transliterate' => $transliterate]);

        $this->assertRegExp($pattern, $namer->name($entity, $mapping));
    }
}
