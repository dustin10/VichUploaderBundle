<?php

namespace Vich\UploaderBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandCompletionTester;
use Vich\UploaderBundle\Command\MappingDebugCommand;
use Vich\UploaderBundle\Exception\MappingNotFoundException;

final class MappingDebugCommandTest extends AbstractCommandTestCase
{
    public function testNotExistentMapping(): void
    {
        $mappings = [];
        $command = new MappingDebugCommand($mappings);
        $this->expectException(MappingNotFoundException::class);
        $this->executeCommand('vich:mapping:debug', $command, ['mapping' => 'foo']);
    }

    public function testExistentMapping(): void
    {
        $mappings = ['image_mapping' => []];
        $command = new MappingDebugCommand($mappings);
        $output = $this->executeCommand('vich:mapping:debug', $command, ['mapping' => 'image_mapping']);
        self::assertStringContainsString('Debug information for mapping image_mapping', $output);
    }

    /**
     * @dataProvider provideCompletionSuggestions
     */
    public function testComplete(array $input, array $expectedSuggestions): void
    {
        if (!\class_exists(CommandCompletionTester::class)) {
            $this->markTestSkipped('Test command completion requires symfony/console 5.4+.');
        }

        $mappings = [
            'product_mapping' => [],
            'taxonomy_mapping' => [],
            'image_mapping' => [],
        ];
        $tester = new CommandCompletionTester(new MappingDebugCommand($mappings));

        $this->assertEqualsCanonicalizing($expectedSuggestions, $tester->complete($input));
    }

    public function provideCompletionSuggestions(): \Generator
    {
        yield 'mapping' => [
            [''],
            [
                'image_mapping',
                'product_mapping',
                'taxonomy_mapping',
            ],
        ];
    }
}
