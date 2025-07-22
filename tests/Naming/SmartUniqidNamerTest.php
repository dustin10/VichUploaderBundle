<?php

namespace Vich\UploaderBundle\Tests\Naming;

use PHPUnit\Framework\Attributes\DataProvider;
use Vich\UploaderBundle\Naming\SmartUniqueNamer;
use Vich\UploaderBundle\Tests\TestCase;

final class SmartUniqidNamerTest extends TestCase
{
    public static function fileDataProvider(): array
    {
        return [
            // case -> original name, guessed extension, result pattern
            'typical' => ['lala.jpeg', 'jpg', '/lala-[[:xdigit:]]{22}\.jpg/'],
            'accented' => ['làlà.mp3', 'mp3', '/lala-[[:xdigit:]]{22}\.mp3/'],
            'spaced' => ['a Foo Bar.txt', 'txt', '/a-foo-bar-[[:xdigit:]]{22}\.txt/'],
            'special char' => ['yezz!.png', 'png', '/yezz-[[:xdigit:]]{22}\.png/'],
            'long basename' => [\str_repeat('a', 256).'.txt', 'txt', '/a{228}-[[:xdigit:]]{22}\.txt/'],
            'long extension' => ['a.'.\str_repeat('a', 256), null, '/a-[[:xdigit:]]{22}$/'],
            'long basename and extension' => [\str_repeat('a', 256).'.txt'.\str_repeat('a', 256),
                'txt', '/a{228}-[[:xdigit:]]{22}\.txt$/', ],
            'double extension' => ['lala.png.jpg', 'jpg', '/lala-png-[[:xdigit:]]{22}\.jpg/'],
            'uppercase extension' => ['lala.JPEG', 'jpg', '/lala-[[:xdigit:]]{22}\.jpg/'],
            'double uppercase extension' => ['lala.JPEG.JPEG', 'jpg', '/lala-jpeg-[[:xdigit:]]{22}\.jpg/'],
            'dot in filename' => ['filename has . spaces (2).jpg', 'jpg', '/filename-has-spaces-2-[[:xdigit:]]{22}\.jpg/'],
            'file with no extension with null mimetype' => ['lala', null, '/lala-[[:xdigit:]]{22}$/'],
            'csv retains extension even if guessed as txt' => ['lala.csv', 'txt', '/lala-[[:xdigit:]]{22}\.csv/'],
            'gpx retains extension even if guessed as xml' => ['baz.gpx', 'xml', '/^baz-[[:xdigit:]]{22}\.gpx$/'],
            'xlsb retains extension even if guessed as xlsx' => ['lala.xlsb', 'xlsx', '/lala-[[:xdigit:]]{22}\.xlsb/'],
        ];
    }

    #[DataProvider('fileDataProvider')]
    public function testNameReturnsAnUniqueName(string $originalName, ?string $guessExtension, string $pattern): void
    {
        $file = $this->getUploadedFileMock();
        $file
            ->method('getClientOriginalName')
            ->willReturn($originalName)
        ;

        $file
            ->expects(self::once())
            ->method('guessExtension')
            ->willReturn($guessExtension)
        ;

        $entity = new \stdClass();

        $mapping = $this->getPropertyMappingMock();
        $mapping->expects(self::once())
            ->method('getFile')
            ->with($entity)
            ->willReturn($file)
        ;

        $namer = new SmartUniqueNamer($this->getTransliterator());

        self::assertMatchesRegularExpression($pattern, $namer->name($entity, $mapping));
    }
}
