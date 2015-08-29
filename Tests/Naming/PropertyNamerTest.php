<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Vich\UploaderBundle\Naming\PropertyNamer;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * PropertyNamerTest.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class PropertyNamerTest extends TestCase
{
    public function fileDataProvider()
    {
        $entity = new DummyEntity();
        $entity->someProperty = 'foo';

        return array(
            array('some-file-name.jpeg', 'foo.jpeg', $entity, 'someProperty'),
            array('some-file-name', 'foo', $entity, 'someProperty'),
            array('some-file-name.jpeg', 'generated-file-name.jpeg', $entity, 'generateFileName'), // method call
        );
    }

    /**
     * @dataProvider fileDataProvider
     */
    public function testNameReturnsTheRightName($originalFileName, $expectedFileName, $entity, $propertyName)
    {
        $file = $this->getUploadedFileMock();
        $file
            ->expects($this->any())
            ->method('getClientOriginalName')
            ->will($this->returnValue($originalFileName));

        $mapping = $this->getPropertyMappingMock();
        $mapping->expects($this->once())
            ->method('getFile')
            ->with($entity)
            ->will($this->returnValue($file));

        $namer = new PropertyNamer();
        $namer->configure(array('property' => $propertyName));

        $this->assertSame($expectedFileName, $namer->name($entity, $mapping));
    }

    /**
     * @expectedException Vich\UploaderBundle\Exception\NameGenerationException
     */
    public function testNameFailsIfThePropertyDoesNotExist()
    {
        $entity  = new DummyEntity();
        $mapping = $this->getPropertyMappingMock();

        $namer = new PropertyNamer();
        $namer->configure(array('property' => 'nonExistentProperty'));

        $namer->name($entity, $mapping);
    }

    /**
     * @expectedException Vich\UploaderBundle\Exception\NameGenerationException
     */
    public function testNameFailsIfThePropertyIsEmpty()
    {
        $mapping = $this->getPropertyMappingMock();
        $namer   = new PropertyNamer();

        $namer->configure(array('property' => 'someProperty'));

        $namer->name(new DummyEntity(), $mapping);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage The property to use can not be determined. Did you call the configure() method?
     */
    public function testNamerNeedsToBeConfigured()
    {
        $mapping = $this->getPropertyMappingMock();
        $namer   = new PropertyNamer();

        $namer->name(new DummyEntity(), $mapping);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Option "property" is missing or empty.
     */
    public function testConfigurationFailsIfThePropertyIsntSpecified()
    {
        $namer = new PropertyNamer();

        $namer->configure(array('incorrect' => 'options'));
    }

    /**
     * @return \Vich\UploaderBundle\Mapping\PropertyMapping
     */
    private function getPropertyMappingMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
