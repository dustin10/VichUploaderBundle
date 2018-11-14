<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Vich\UploaderBundle\Naming\UniqidNamer;
use Vich\UploaderBundle\Tests\TestCase;
use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * UniqidNamerTest.
 *
 * @author Emmanuel Vella <vella.emmanuel@gmail.com>
 */
class UniqidNamerTest extends TestCase
{
    public function fileDataProvider(): array
    {
        return [
            // original_name, guessed_extension, pattern
            ['lala.jpeg',     null,              '/[a-z0-9]{13}.jpeg/'],
            ['lala.mp3',      'mpga',            '/[a-z0-9]{13}.mp3/'],
            ['lala',          'mpga',            '/[a-z0-9]{13}.mpga/'],
            ['lala',          null,              '/[a-z0-9]{13}/'],
        ];
    }

    /**
     * @dataProvider fileDataProvider
     */
    public function testNameReturnsAnUniqueName($originalName, $guessedExtension, $pattern): void
    {
        $file = $this->getUploadedFileMock();
        $file
            ->expects($this->any())
            ->method('getClientOriginalName')
            ->will($this->returnValue($originalName));
        $file
            ->expects($this->any())
            ->method('guessExtension')
            ->will($this->returnValue($guessedExtension));

        $entity = new \DateTime();

        $mapping = $this->getMockBuilder( PropertyMapping::class )
            ->disableOriginalConstructor()
            ->getMock();
        $mapping->expects($this->once())
            ->method('getFile')
            ->with($entity)
            ->will($this->returnValue($file));

        $namer = new UniqidNamer();

        $this->assertRegExp($pattern, $namer->name($entity, $mapping));
    }
}
