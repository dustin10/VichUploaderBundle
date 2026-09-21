<?php

namespace Vich\UploaderBundle\Tests\Naming;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Vich\UploaderBundle\Naming\ChainDirectoryNamer;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Naming\SubdirDirectoryNamer;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * @author Guillaume Loulier
 */
final class ChainDirectoryNamerTest extends TestCase
{
    /**
     * withOptions() is public API: the children it receives may well be shared services, so the
     * chain has to copy them whether they come from the resolver or from a direct call.
     */
    #[Test]
    public function childrenGivenToWithOptionsAreCopied(): void
    {
        $child = new SubdirDirectoryNamer();
        $child->configure(['dirs' => 1]);

        $chain = new ChainDirectoryNamer();
        $first = $chain->withOptions(['namers' => [$child]]);
        $second = $chain->withOptions(['namers' => [$child]]);

        $child->configure(['dirs' => 3]);

        $entity = new DummyEntity();
        $entity->setFileName('0123456789.jpg');
        $mapping = $this->getPropertyMappingStub();
        $mapping->method('getFileName')->willReturn('0123456789.jpg');

        self::assertSame('01', $first->directoryName($entity, $mapping));
        self::assertSame('01', $second->directoryName($entity, $mapping));
    }

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
    #[Test]
    public function directoryNameChainsNamers(array $namerResults, string $separator, string $expected): void
    {
        $entity = new DummyEntity();
        $mapping = $this->getPropertyMappingStub();

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

    #[Test]
    public function defaultSeparatorIsSlash(): void
    {
        $entity = new DummyEntity();
        $mapping = $this->getPropertyMappingStub();

        $namer1 = $this->createStub(DirectoryNamerInterface::class);
        $namer1->method('directoryName')->willReturn('a');

        $namer2 = $this->createStub(DirectoryNamerInterface::class);
        $namer2->method('directoryName')->willReturn('b');

        $chainNamer = new ChainDirectoryNamer();
        $chainNamer->setNamers([$namer1, $namer2]);

        self::assertSame('a/b', $chainNamer->directoryName($entity, $mapping));
    }

    #[Test]
    public function configureWithoutSeparatorKeepsDefault(): void
    {
        $entity = new DummyEntity();
        $mapping = $this->getPropertyMappingStub();

        $namer1 = $this->createStub(DirectoryNamerInterface::class);
        $namer1->method('directoryName')->willReturn('a');

        $namer2 = $this->createStub(DirectoryNamerInterface::class);
        $namer2->method('directoryName')->willReturn('b');

        $chainNamer = new ChainDirectoryNamer();
        $chainNamer->setNamers([$namer1, $namer2]);
        $chainNamer->configure([]); // Empty options should not change the default separator

        self::assertSame('a/b', $chainNamer->directoryName($entity, $mapping));
    }

    #[Test]
    public function emptyStringFromNamerIsFiltered(): void
    {
        $entity = new DummyEntity();
        $mapping = $this->getPropertyMappingStub();

        $namer1 = $this->createStub(DirectoryNamerInterface::class);
        $namer1->method('directoryName')->willReturn('start');

        $namer2 = $this->createStub(DirectoryNamerInterface::class);
        $namer2->method('directoryName')->willReturn('');

        $namer3 = $this->createStub(DirectoryNamerInterface::class);
        $namer3->method('directoryName')->willReturn('end');

        $chainNamer = new ChainDirectoryNamer();
        $chainNamer->setNamers([$namer1, $namer2, $namer3]);

        self::assertSame('start/end', $chainNamer->directoryName($entity, $mapping));
    }
}
