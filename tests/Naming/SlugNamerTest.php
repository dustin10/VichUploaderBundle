<?php

namespace Vich\UploaderBundle\Tests\Naming;

use PHPUnit\Framework\Attributes\DataProvider;
use Vich\UploaderBundle\Naming\SlugNamer;
use Vich\UploaderBundle\Tests\TestCase;

final class SlugNamerTest extends TestCase
{
    public static function fileDataProvider(): array
    {
        return [
            // case -> original name, guessedExtension, result pattern
            'non existing' => ['lala.jpeg', 'jpg', '/lala.jpg/'],
            'guess extension null' => ['lala.jpeg', null, '/lala$/'],
            'existing' => ['làlà.mp3', 'mp3', '/lala-1.mp3/'],
        ];
    }

    #[DataProvider('fileDataProvider')]
    public function testNameReturnsAnUniqueName(string $originalName, ?string $guessedExtension, string $pattern): void
    {
        $file = $this->getUploadedFileMock();
        $file
            ->expects(self::once())
            ->method('getClientOriginalName')
            ->willReturn($originalName)
        ;

        $file
            ->expects(self::once())
            ->method('guessExtension')
            ->willReturn($guessedExtension)
        ;

        $entity = new \stdClass();

        $mapping = $this->getPropertyMappingMock();
        $mapping->expects(self::once())
            ->method('getFile')
            ->with($entity)
            ->willReturn($file)
        ;

        $repo = new class() {
            public function findOneBySlug(string $slug): ?object
            {
                return 'lala.mp3' === $slug ? new \stdClass() : null;
            }
        };

        $namer = new SlugNamer($this->getTransliterator(), $repo, 'findOneBySlug');

        self::assertMatchesRegularExpression($pattern, $namer->name($entity, $mapping));
    }
}
