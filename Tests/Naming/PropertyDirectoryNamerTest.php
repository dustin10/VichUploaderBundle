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
    public function fileDataProvider()
    {
        $entity = new DummyEntity();
        $entity->someProperty = 'foo';

        $weird_entity = new DummyEntity();
        $weird_entity->someProperty = 'Yéô';

        return [
            ['foo',                      $entity,       'someProperty',     false],
            ['generated-file-name',      $entity,       'generateFileName', false], // method call
            ['yeo',                      $weird_entity, 'someProperty',     true],  // transliteration enabled
        ];
    }

    /**
     * @dataProvider fileDataProvider
     */
    public function testNameReturnsTheRightName($expectedDirectoryName, $entity, $propertyName, $transliterate)
    {
        $mapping = $this->getPropertyMappingMock();

        $namer = new PropertyDirectoryNamer();
        $namer->configure(['property' => $propertyName, 'transliterate' => $transliterate]);

        $this->assertSame($expectedDirectoryName, $namer->directoryName($entity, $mapping));
    }

    /**
     * @expectedException \Vich\UploaderBundle\Exception\NameGenerationException
     */
    public function testNameFailsIfThePropertyDoesNotExist()
    {
        $entity = new DummyEntity();
        $mapping = $this->getPropertyMappingMock();

        $namer = new PropertyDirectoryNamer();
        $namer->configure(['property' => 'nonExistentProperty']);

        $namer->directoryName($entity, $mapping);
    }

    /**
     * @expectedException \Vich\UploaderBundle\Exception\NameGenerationException
     */
    public function testNameFailsIfThePropertyIsEmpty()
    {
        $mapping = $this->getPropertyMappingMock();
        $namer = new PropertyDirectoryNamer();

        $namer->configure(['property' => 'someProperty']);

        $namer->directoryName(new DummyEntity(), $mapping);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The property to use can not be determined. Did you call the configure() method?
     */
    public function testNamerNeedsToBeConfigured()
    {
        $mapping = $this->getPropertyMappingMock();
        $namer = new PropertyDirectoryNamer();

        $namer->directoryName(new DummyEntity(), $mapping);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Option "property" is missing or empty.
     */
    public function testConfigurationFailsIfThePropertyIsntSpecified()
    {
        $namer = new PropertyDirectoryNamer();

        $namer->configure(['incorrect' => 'options']);
    }
}
