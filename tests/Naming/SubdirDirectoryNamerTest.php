<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Vich\UploaderBundle\Naming\SubdirDirectoryNamer;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 */
final class SubdirDirectoryNamerTest extends TestCase
{
    public static function fileDataProvider(): array
    {
        return [
            ['0123456789.jpg', '01', 2, 1],
            ['0123456789.jpg', '01/23', 2, 2],
            ['0123456789.jpg', '012', 3, 1],
            ['0123456789.jpg', '0', 1, 1],
            ['0123456789.jpg', '0/1/2/3', 1, 4],
        ];
    }

    /**
     * @dataProvider fileDataProvider
     */
    public function testNameReturnsTheRightName(string $fileName, string $expectedFileName, int $charsPerDir, int $dirs): void
    {
        $entity = new DummyEntity();
        $entity->setFileName($fileName);

        $mapping = $this->getPropertyMappingMock();
        $mapping->expects(self::once())
            ->method('getFileName')
            ->with($entity)
            ->willReturn($fileName);

        $namer = new SubdirDirectoryNamer();
        $namer->configure(['chars_per_dir' => $charsPerDir, 'dirs' => $dirs]);

        self::assertSame($expectedFileName, $namer->directoryName($entity, $mapping));
    }
}
