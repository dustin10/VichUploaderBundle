<?php

namespace Vich\UploaderBundle\Tests\Command;

use Vich\TestBundle\Entity\Image;
use Vich\UploaderBundle\Command\MappingListClassesCommand;
use Vich\UploaderBundle\Metadata\MetadataReader;

final class MappingListClassesCommandTest extends AbstractCommandTestCase
{
    public function testListClasses(): void
    {
        $reader = $this->createMock(MetadataReader::class);
        $reader->expects($this->once())->method('getUploadableClasses')->willReturn([Image::class]);
        $command = new MappingListClassesCommand($reader);
        $output = $this->executeCommand('vich:mapping:list-classes', $command);
        $this->assertStringContainsString('Found Vich\TestBundle\Entity\Image', $output);
    }
}
