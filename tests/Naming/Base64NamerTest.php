<?php

namespace Vich\UploaderBundle\Tests\Naming;

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

    /**
     * @dataProvider fileDataProvider
     */
    public function testNameReturnsTheRightName(string $expectedFileName, string $extension, ?int $length): void
    {
        $file = $this->getUploadedFileMock();
        $file->expects(self::once())
            ->method('getClientOriginalName')
            ->willReturn('foo');

        $file->expects(self::once())
            ->method('guessExtension')
            ->willReturn($extension);

        $entity = new DummyEntity();
        $entity->setFile($file);

        $mapping = $this->getPropertyMappingMock();
        $mapping->expects(self::once())
            ->method('getFile')
            ->with($entity)
            ->willReturn($file);

        $namer = new Base64Namer();
        $namer->configure(['length' => $length]);

        self::assertSame($expectedFileName, $namer->name($entity, $mapping));
    }
}
