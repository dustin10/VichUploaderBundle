<?php

namespace Vich\UploaderBundle\Tests\Naming;

use PHPUnit\Framework\Attributes\Test;
use Vich\UploaderBundle\Naming\ConfigurableDirectoryNamer;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

final class ConfigurableDirectoryNamerTest extends TestCase
{
    #[Test]
    public function nameReturnsTheRightName(): void
    {
        $entity = new DummyEntity();
        $entity->setFileName('file name');
        $mapping = $this->getPropertyMappingMock();

        $namer = new ConfigurableDirectoryNamer();
        $namer->configure(['directory_path' => 'folder/subfolder/subsubfolder']);

        self::assertSame('folder/subfolder/subsubfolder', $namer->directoryName($entity, $mapping));
    }

    #[Test]
    public function configurationFailsIfTheDirectoryPathIsntSpecified(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Option "directory_path" is missing.');

        $namer = new ConfigurableDirectoryNamer();

        $namer->configure(['incorrect' => 'options']);
    }
}
