<?php

namespace Vich\UploaderBundle\Tests\Command;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Tester\CommandCompletionTester;
use Vich\TestBundle\Entity\Image;
use Vich\UploaderBundle\Command\MappingDebugClassCommand;

final class MappingDebugClassCommandTest extends AbstractCommandTestCase
{
    #[Test]
    public function notUploadableClass(): void
    {
        $reader = $this->mockMetadataReader();
        $reader->expects($this->once())->method('isUploadable')->willReturn(false);
        $command = new MappingDebugClassCommand($reader);
        $output = $this->executeCommand('vich:mapping:debug-class', $command, ['fqcn' => \stdClass::class]);
        self::assertStringContainsString('is not uploadable', $output);
    }

    #[Test]
    public function uploadableClass(): void
    {
        $reader = $this->mockMetadataReader();
        $reader->expects($this->once())->method('isUploadable')->willReturn(true);
        $command = new MappingDebugClassCommand($reader);
        $output = $this->executeCommand('vich:mapping:debug-class', $command, ['fqcn' => Image::class]);
        self::assertStringContainsString('Introspecting class', $output);
    }

    #[DataProvider('provideCompletionSuggestions')]
    #[Test]
    public function complete(array $input, array $expectedSuggestions): void
    {
        if (!\class_exists(CommandCompletionTester::class)) {
            self::markTestSkipped('Test command completion requires symfony/console 5.4+.');
        }

        $reader = $this->mockMetadataReader();
        $reader->expects($this->once())->method('getUploadableClasses')->willReturn([Image::class]);
        $tester = new CommandCompletionTester(new MappingDebugClassCommand($reader));

        $this->assertEqualsCanonicalizing($expectedSuggestions, $tester->complete($input));
    }

    public static function provideCompletionSuggestions(): \Generator
    {
        yield 'fqcn' => [
            [''],
            [
                Image::class,
            ],
        ];
    }
}
