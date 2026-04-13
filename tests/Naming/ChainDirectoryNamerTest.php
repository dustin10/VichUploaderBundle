<?php

namespace Vich\UploaderBundle\Tests\Naming;

use PHPUnit\Framework\Attributes\DataProvider;
use Vich\UploaderBundle\Naming\ChainDirectoryNamer;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * @author Guillaume Loulier
 */
final class ChainDirectoryNamerTest extends TestCase
{
    public static function chainDataProvider(): array
    {
        return [
            'two namers' => [['dir1', 'dir2'], '/', 'dir1/dir2'],
            'three namers' => [['a', 'b', 'c'], '/', 'a/b/c'],
            'custom separator' => [['a', 'b'], '-', 'a-b'],
            'with empty values' => [['a', '', 'c'], '/', 'a/c'],
            'single namer' => [['only'], '/', 'only'],
            'no namers' => [[], '/', ''],
        ];
    }

    #[DataProvider('chainDataProvider')]
    public function testDirectoryNameChainsNamers(array $namerResults, string $separator, string $expected): void
    {
        $entity = new DummyEntity();
        $mapping = $this->getPropertyMappingMock();

        $namers = [];
        foreach ($namerResults as $result) {
            $namer = $this->createMock(DirectoryNamerInterface::class);
            $namer->expects(self::once())
                ->method('directoryName')
                ->with($entity, $mapping)
                ->willReturn($result);
            $namers[] = $namer;
        }

        $chainNamer = new ChainDirectoryNamer();
        $chainNamer->setNamers($namers);
        $chainNamer->configure(['separator' => $separator]);

        self::assertSame($expected, $chainNamer->directoryName($entity, $mapping));
    }

    public function testDefaultSeparatorIsSlash(): void
    {
        $entity = new DummyEntity();
        $mapping = $this->getPropertyMappingMock();

        $namer1 = $this->createMock(DirectoryNamerInterface::class);
        $namer1->method('directoryName')->willReturn('a');

        $namer2 = $this->createMock(DirectoryNamerInterface::class);
        $namer2->method('directoryName')->willReturn('b');

        $chainNamer = new ChainDirectoryNamer();
        $chainNamer->setNamers([$namer1, $namer2]);

        self::assertSame('a/b', $chainNamer->directoryName($entity, $mapping));
    }

    public function testConfigureWithoutSeparatorKeepsDefault(): void
    {
        $entity = new DummyEntity();
        $mapping = $this->getPropertyMappingMock();

        $namer1 = $this->createMock(DirectoryNamerInterface::class);
        $namer1->method('directoryName')->willReturn('a');

        $namer2 = $this->createMock(DirectoryNamerInterface::class);
        $namer2->method('directoryName')->willReturn('b');

        $chainNamer = new ChainDirectoryNamer();
        $chainNamer->setNamers([$namer1, $namer2]);
        $chainNamer->configure([]); // Empty options should not change the default separator

        self::assertSame('a/b', $chainNamer->directoryName($entity, $mapping));
    }

    public function testEmptyStringFromNamerIsFiltered(): void
    {
        $entity = new DummyEntity();
        $mapping = $this->getPropertyMappingMock();

        $namer1 = $this->createMock(DirectoryNamerInterface::class);
        $namer1->method('directoryName')->willReturn('start');

        $namer2 = $this->createMock(DirectoryNamerInterface::class);
        $namer2->method('directoryName')->willReturn('');

        $namer3 = $this->createMock(DirectoryNamerInterface::class);
        $namer3->method('directoryName')->willReturn('end');

        $chainNamer = new ChainDirectoryNamer();
        $chainNamer->setNamers([$namer1, $namer2, $namer3]);

        self::assertSame('start/end', $chainNamer->directoryName($entity, $mapping));
    }
}
