<?php

namespace Vich\UploaderBundle\Tests\Command;

use Vich\UploaderBundle\Command\MappingDebugCommand;
use Vich\UploaderBundle\Exception\MappingNotFoundException;

final class MappingDebugCommandTest extends AbstractCommandTestCase
{
    public function testNotExistantMapping(): void
    {
        $mappings = [];
        $command = new MappingDebugCommand($mappings);
        $this->expectException(MappingNotFoundException::class);
        $this->executeCommand('vich:mapping:debug', $command, ['mapping' => 'foo']);
    }

    public function testExistantMapping(): void
    {
        $mappings = ['image_mapping' => []];
        $command = new MappingDebugCommand($mappings);
        $output = $this->executeCommand('vich:mapping:debug', $command, ['mapping' => 'image_mapping']);
        $this->assertStringContainsString('Debug information for mapping image_mapping', $output);
    }
}
