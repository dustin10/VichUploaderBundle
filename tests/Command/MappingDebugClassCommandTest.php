<?php

namespace Vich\UploaderBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandCompletionTester;
use Vich\TestBundle\Entity\Image;
use Vich\UploaderBundle\Command\MappingDebugClassCommand;

final class MappingDebugClassCommandTest extends AbstractCommandTestCase
{
    public function testNotUploadableClass(): void
    {
        $reader = $this->mockMetadataReader();
        $reader->expects(self::once())->method('isUploadable')->willReturn(false);
        $command = new MappingDebugClassCommand($reader);
        $output = $this->executeCommand('vich:mapping:debug-class', $command, ['fqcn' => \stdClass::class]);
        self::assertStringContainsString('is not uploadable', $output);
    }

    public function testUploadableClass(): void
    {
        $reader = $this->mockMetadataReader();
        $reader->expects(self::once())->method('isUploadable')->willReturn(true);
        $command = new MappingDebugClassCommand($reader);
        $output = $this->executeCommand('vich:mapping:debug-class', $command, ['fqcn' => Image::class]);
        self::assertStringContainsString('Introspecting class', $output);
    }

    /**
     * @dataProvider provideCompletionSuggestions
     */
    public function testComplete(array $input, array $expectedSuggestions): void
    {
        if (!\class_exists(CommandCompletionTester::class)) {
            $this->markTestSkipped('Test command completion requires symfony/console 5.4+.');
        }

        $reader = $this->mockMetadataReader();
        $reader->expects(self::once())->method('getUploadableClasses')->willReturn([Image::class]);
        $tester = new CommandCompletionTester(new MappingDebugClassCommand($reader));

        $this->assertEqualsCanonicalizing($expectedSuggestions, $tester->complete($input));
    }

    public function provideCompletionSuggestions(): \Generator
    {
        yield 'fqcn' => [
            [''],
            [
                Image::class,
            ],
        ];
    }
}
