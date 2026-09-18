<?php

namespace Vich\UploaderBundle\Tests\Command;

use PHPUnit\Framework\Attributes\Test;
use Vich\TestBundle\Entity\Image;
use Vich\UploaderBundle\Command\MappingListClassesCommand;

final class MappingListClassesCommandTest extends AbstractCommandTestCase
{
    #[Test]
    public function listClasses(): void
    {
        $reader = $this->mockMetadataReader();
        $reader->expects($this->once())->method('getUploadableClasses')->willReturn([Image::class]);
        $command = new MappingListClassesCommand($reader);
        $output = $this->executeCommand('vich:mapping:list-classes', $command);
        self::assertStringContainsString('Found Vich\TestBundle\Entity\Image', $output);
    }
}
