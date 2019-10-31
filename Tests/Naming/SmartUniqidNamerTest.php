<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\SmartUniqueNamer;
use Vich\UploaderBundle\Tests\TestCase;

final class SmartUniqidNamerTest extends TestCase
{
    public function fileDataProvider(): array
    {
        return [
            // case -> original name, result pattern
            'typical' => ['lala.jpeg', '/lala-[[:xdigit:]]{22}\.jpeg/'],
            'accented' => ['làlà.mp3', '/lala-[[:xdigit:]]{22}\.mp3/'],
            'spaced' => ['a Foo Bar.txt', '/a-foo-bar-[[:xdigit:]]{22}\.txt/'],
            'special char' => ['yezz!.png', '/yezz-[[:xdigit:]]{22}\.png/'],
            'long basename' => [\str_repeat('a', 230).'.txt', '/a{229}[[:xdigit:]]{22}\.txt/'],
            'long extension' => ['a.'.\str_repeat('a', 232), '/a[[:xdigit:]]{22}\.a{231}/'],
            'long basename and extension' => [\str_repeat('a', 237).'.txt'.\str_repeat('a', 256),
                                              '/[[:xdigit:]]{22}\.txt/', ],
        ];
    }

    /**
     * @dataProvider fileDataProvider
     */
    public function testNameReturnsAnUniqueName(string $originalName, string $pattern): void
    {
        $file = $this->getUploadedFileMock();
        $file
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->willReturn($originalName)
        ;

        $entity = new \stdClass();

        $mapping = $this->getMockBuilder(PropertyMapping::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $mapping->expects($this->once())
            ->method('getFile')
            ->with($entity)
            ->willReturn($file)
        ;

        $namer = new SmartUniqueNamer();

        $this->assertRegExp($pattern, $namer->name($entity, $mapping));
    }
}
