<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Vich\UploaderBundle\Naming\OrignameNamer;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * OrignameNamerTest.
 *
 * @author Ivan Borzenkov <ivan.borzenkov@gmail.com>
 */
final class OrignameNamerTest extends TestCase
{
    public static function fileDataProvider(): array
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
    public function testNameReturnsAnUniqueName(string $name, string $pattern, bool $transliterate): void
    {
        $file = $this->getUploadedFileMock();
        $file
            ->method('getClientOriginalName')
            ->willReturn($name);

        $entity = new \DateTime();

        $mapping = $this->getPropertyMappingMock();
        $mapping->expects(self::once())
            ->method('getFile')
            ->with($entity)
            ->willReturn($file);

        $namer = new OrignameNamer($this->getTransliterator());
        $namer->configure(['transliterate' => $transliterate]);

        self::assertMatchesRegularExpression($pattern, $namer->name($entity, $mapping));
    }
}
