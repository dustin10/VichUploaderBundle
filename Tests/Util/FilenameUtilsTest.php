<?php

namespace Vich\UploaderBundle\Tests\Util;

use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Util\FilenameUtils;

class FilenameUtilsTest extends TestCase
{
    /**
     * @dataProvider spitNameByExtensionProvider
     */
    public function testSpitNameByExtension($filename, $basename, $extension): void
    {
        $this->assertSame([$basename, $extension], FilenameUtils::spitNameByExtension($filename));
    }

    public function spitNameByExtensionProvider(): array
    {
        return [
            'simple filename with extension' => ['filename.extension', 'filename', 'extension'],
            'cyrillic filename with extension  ' => ['Текстовый файл.txt', 'Текстовый файл', 'txt'],
            'cyrillic filename with dot and extension' => ['Текстовый .файл.txt', 'Текстовый .файл', 'txt'],
            'cyrillic filename without extension ends with dot' => ['Текстовый файл.', 'Текстовый файл', ''],
            'cyrillic filename without extension' => ['Текстовый файл', 'Текстовый файл', ''],
        ];
    }
}
