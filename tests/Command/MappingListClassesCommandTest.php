<?php

namespace Vich\UploaderBundle\Tests\Command;

use Vich\TestBundle\Entity\Image;
use Vich\UploaderBundle\Command\MappingListClassesCommand;

final class MappingListClassesCommandTest extends AbstractCommandTestCase
{
    public function testListClasses(): void
    {
        $reader = $this->mockMetadataReader();
        $reader->expects(self::once())->method('getUploadableClasses')->willReturn([Image::class]);
        $command = new MappingListClassesCommand($reader);
        $output = $this->executeCommand('vich:mapping:list-classes', $command);
        self::assertStringContainsString('Found Vich\TestBundle\Entity\Image', $output);
    }
}
