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

        $mapping = $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
            ->disableOriginalConstructor()
            ->getMock();
        $mapping->expects($this->once())
            ->method('getFile')
            ->with($entity)
            ->will($this->returnValue($file));

        $namer = new PropertyNamer($propertyName);

        $this->assertSame($expectedFileName, $namer->name($entity, $mapping));
    }

    /**
     * @expectedException Vich\UploaderBundle\Exception\NameGenerationException
     */
    public function testNameFailsIfThePropertyDoesNotExist()
    {
        $entity  = new DummyEntity();
        $file    = $this->getUploadedFileMock();
        $mapping = $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
            ->disableOriginalConstructor()
            ->getMock();

        $namer = new PropertyNamer('nonExistentProperty');

        $namer->name($entity, $mapping);
    }

    /**
     * @expectedException Vich\UploaderBundle\Exception\NameGenerationException
     */
    public function testNameFailsIfThePropertyIsEmpty()
    {
        $entity  = new DummyEntity();
        $file    = $this->getUploadedFileMock();
        $mapping = $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
            ->disableOriginalConstructor()
            ->getMock();

        $namer = new PropertyNamer('someProperty');

        $namer->name($entity, $mapping);
    }
}
