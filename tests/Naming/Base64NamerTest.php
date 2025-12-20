<?php

namespace Vich\UploaderBundle\Tests\Naming;

use PHPUnit\Framework\Attributes\DataProvider;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

class Base64Namer extends \Vich\UploaderBundle\Naming\Base64Namer
{
    protected function getRandomChar(): string
    {
        return 'a';
    }
}

/**
 * @author Keleti Márton <tejes@hac.hu>
 */
class Base64NamerTest extends TestCase
{
    public static function fileDataProvider(): array
    {
        return [
            ['aaaaaaaaaa.jpg', 'jpg', null],
            ['aaaaaaaaaa', '', null],
            ['aaaaaaaa.jpg', 'jpg', 8],
            ['aaaaaaaaaaaaaaaa.png', 'png', 16],
        ];
    }

    #[DataProvider('fileDataProvider')]
    public function testNameReturnsTheRightName(string $expectedFileName, string $extension, ?int $length): void
    {
        $file = $this->getUploadedFileMock();

        $file->expects($this->once())
            ->method('guessExtension')
            ->willReturn($extension);

        $entity = new DummyEntity();
        $entity->setFile($file);

        $mapping = $this->getPropertyMappingMock();
        $mapping->expects($this->once())
            ->method('getFile')
            ->with($entity)
            ->willReturn($file);

        $namer = new Base64Namer();
        $namer->configure(['length' => $length]);

        self::assertSame($expectedFileName, $namer->name($entity, $mapping));
    }
}
