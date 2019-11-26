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
    public function fileDataProvider(): array
    {
        $entity = new DummyEntity();
        $entity->someProperty = 'foo';

        $weird_entity = new DummyEntity();
        $weird_entity->someProperty = 'Yéô';

        return [
            ['some-file-name.jpeg', 'foo.jpeg',                 $entity,       'someProperty',     false],
            ['some-file-name',      'foo',                      $entity,       'someProperty',     false],
            ['some-file-name.jpeg', 'generated-file-name.jpeg', $entity,       'generateFileName', false], // method call
            ['some-file-name.jpeg', 'yeo.jpeg',                 $weird_entity, 'someProperty',     true],  // transliteration enabled
        ];
    }

    /**
     * @dataProvider fileDataProvider
     */
    public function testNameReturnsTheRightName($originalFileName, $expectedFileName, $entity, $propertyName, $transliterate): void
    {
        $file = $this->getUploadedFileMock();
        $file
            ->expects($this->any())
            ->method('getClientOriginalName')
            ->willReturn($originalFileName);

        $mapping = $this->getPropertyMappingMock();
        $mapping->expects($this->once())
            ->method('getFile')
            ->with($entity)
            ->willReturn($file);

        $namer = new PropertyNamer();
        $namer->configure(['property' => $propertyName, 'transliterate' => $transliterate]);

        $this->assertSame($expectedFileName, $namer->name($entity, $mapping));
    }

    public function testNameFailsIfThePropertyDoesNotExist(): void
    {
        $this->expectException(\Vich\UploaderBundle\Exception\NameGenerationException::class);

        $entity = new DummyEntity();
        $mapping = $this->getPropertyMappingMock();

        $namer = new PropertyNamer();
        $namer->configure(['property' => 'nonExistentProperty']);

        $namer->name($entity, $mapping);
    }

    public function testNameFailsIfThePropertyIsEmpty(): void
    {
        $this->expectException(\Vich\UploaderBundle\Exception\NameGenerationException::class);

        $mapping = $this->getPropertyMappingMock();
        $namer = new PropertyNamer();

        $namer->configure(['property' => 'someProperty']);

        $namer->name(new DummyEntity(), $mapping);
    }

    public function testNamerNeedsToBeConfigured(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The property to use can not be determined. Did you call the configure() method?');

        $mapping = $this->getPropertyMappingMock();
        $namer = new PropertyNamer();

        $namer->name(new DummyEntity(), $mapping);
    }

    public function testConfigurationFailsIfThePropertyIsntSpecified(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Option "property" is missing or empty.');

        $namer = new PropertyNamer();

        $namer->configure(['incorrect' => 'options']);
    }
}
