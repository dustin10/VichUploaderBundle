<?php

namespace Vich\UploaderBundle\Tests\Naming;

use PHPUnit\Framework\Attributes\DataProvider;
use Vich\UploaderBundle\Naming\OrignameNamer;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * @author Ivan Borzenkov <ivan.borzenkov@gmail.com>
 */
final class OrignameNamerTest extends TestCase
{
    /**
     * @return array<array{string, string, string, bool}>
     */
    public static function fileDataProvider(): array
    {
        return [
            ['file.jpeg', 'jpeg', '/^[a-z0-9]{13}_file.jpeg$/',     false],
            ['file',      'png',  '/^[a-z0-9]{13}_file.png$/',      false],
            ['Yéöù.jpeg', 'jpg',  '/^[a-z0-9]{13}_yeou.jpeg.jpg$/', true],
        ];
    }

    #[DataProvider('fileDataProvider')]
    public function testNameReturnsAnUniqueName(string $name, string $ext, string $pattern, bool $transliterate): void
    {
        $file = $this->getUploadedFileMock();
        $file
            ->method('getClientOriginalName')
            ->willReturn($name);
        $file
            ->method('guessExtension')
            ->willReturn($ext);

        $entity = new \DateTime();

        $mapping = $this->getPropertyMappingMock();
        $mapping->expects($this->once())
            ->method('getFile')
            ->with($entity)
            ->willReturn($file);

        $namer = new OrignameNamer($this->getTransliterator());
        $namer->configure(['transliterate' => $transliterate]);

        self::assertMatchesRegularExpression($pattern, $namer->name($entity, $mapping));
    }
}
