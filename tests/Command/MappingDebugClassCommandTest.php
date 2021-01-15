<?php

namespace Vich\UploaderBundle\Tests\Command;

use Vich\TestBundle\Entity\Image;
use Vich\UploaderBundle\Command\MappingDebugClassCommand;
use Vich\UploaderBundle\Metadata\MetadataReader;

final class MappingDebugClassCommandTest extends AbstractCommandTestCase
{
    public function testNotUploadableClass(): void
    {
        $reader = $this->createMock(MetadataReader::class);
        $reader->expects(self::once())->method('isUploadable')->willReturn(false);
        $command = new MappingDebugClassCommand($reader);
        $output = $this->executeCommand('vich:mapping:debug-class', $command, ['fqcn' => 'stdClass']);
        self::assertStringContainsString('is not uploadable', $output);
    }

    public function testUploadableClass(): void
    {
        $reader = $this->createMock(MetadataReader::class);
        $reader->expects(self::once())->method('isUploadable')->willReturn(true);
        $command = new MappingDebugClassCommand($reader);
        $output = $this->executeCommand('vich:mapping:debug-class', $command, ['fqcn' => Image::class]);
        self::assertStringContainsString('Introspecting class', $output);
    }
}
