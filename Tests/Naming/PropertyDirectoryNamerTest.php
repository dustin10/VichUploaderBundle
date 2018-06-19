<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Vich\UploaderBundle\Naming\PropertyDirectoryNamer;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * PropertyNamerTest.
 *
 * @author Raynald Coupé <raynald@easi-services.fr>
 */
class PropertyDirectoryNamerTest extends TestCase
{
    public function fileDataProvider(): array
    {
        $entity = new DummyEntity();
        $entity->someProperty = 'foo';

        $weird_entity = new DummyEntity();
        $weird_entity->someProperty = 'Yéô';

        return [
            ['foo',                 $entity,       'someProperty',     false],
            ['generated-file-name', $entity,       'generateFileName', false], // method call
            ['yeo',                 $weird_entity, 'someProperty',     true],  // transliteration enabled
        ];
    }

    /**
     * @dataProvider fileDataProvider
     */
    public function testNameReturnsTheRightName($expectedDirectoryName, $entity, $propertyName, $transliterate): void
    {
        $mapping = $this->getPropertyMappingMock();

        $namer = new PropertyDirectoryNamer();
        $namer->configure(['property' => $propertyName, 'transliterate' => $transliterate]);

        $this->assertSame($expectedDirectoryName, $namer->directoryName($entity, $mapping));
    }

    public function testNameFailsIfThePropertyDoesNotExist(): void
    {
        $this->expectException(\Vich\UploaderBundle\Exception\NameGenerationException::class);

        $entity = new DummyEntity();
        $mapping = $this->getPropertyMappingMock();

        $namer = new PropertyDirectoryNamer();
        $namer->configure(['property' => 'nonExistentProperty']);

        $namer->directoryName($entity, $mapping);
    }

    public function testNameFailsIfThePropertyIsEmpty(): void
    {
        $this->expectException(\Vich\UploaderBundle\Exception\NameGenerationException::class);

        $mapping = $this->getPropertyMappingMock();
        $namer = new PropertyDirectoryNamer();

        $namer->configure(['property' => 'someProperty']);

        $namer->directoryName(new DummyEntity(), $mapping);
    }

    public function testNamerNeedsToBeConfigured(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The property to use can not be determined. Did you call the configure() method?');

        $mapping = $this->getPropertyMappingMock();
        $namer = new PropertyDirectoryNamer();

        $namer->directoryName(new DummyEntity(), $mapping);
    }

    public function testConfigurationFailsIfThePropertyIsntSpecified(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Option "property" is missing or empty.');

        $namer = new PropertyDirectoryNamer();

        $namer->configure(['incorrect' => 'options']);
    }
}
