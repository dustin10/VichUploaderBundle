<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Vich\UploaderBundle\Naming\UniqidNamer;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * UniqidNamerTest.
 *
 * @author Emmanuel Vella <vella.emmanuel@gmail.com>
 */
final class UniqidNamerTest extends TestCase
{
    public static function fileDataProvider(): array
    {
        return [
            // original_name, guessed_extension, pattern
            ['lala.jpeg',     null,              '/[a-z0-9]{13}.jpeg/'],
            ['lala.mp3',      'mpga',            '/[a-z0-9]{13}.mp3/'],
            ['lala',          'mpga',            '/[a-z0-9]{13}.mpga/'],
            ['lala',          null,              '/[a-z0-9]{13}/'],
            ['lala.0',        null,              '/[a-z0-9]{13}\\.0/'],
            ['lala.data.0',   null,              '/[a-z0-9]{13}\\.0/'],
            ['lala.data.0',   'gzip',            '/[a-z0-9]{13}\\.0/'],
        ];
    }

    /**
     * @dataProvider fileDataProvider
     */
    public function testNameReturnsAnUniqueName(string $originalName, ?string $guessedExtension, string $pattern): void
    {
        $file = $this->getUploadedFileMock();
        $file
            ->method('getClientOriginalName')
            ->willReturn($originalName);
        $file
            ->method('guessExtension')
            ->willReturn($guessedExtension);

        $entity = new \DateTime();

        $mapping = $this->getPropertyMappingMock();
        $mapping->expects(self::once())
            ->method('getFile')
            ->with($entity)
            ->willReturn($file);

        $namer = new UniqidNamer();

        self::assertMatchesRegularExpression($pattern, $namer->name($entity, $mapping));
    }
}
