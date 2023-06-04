<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Vich\UploaderBundle\Exception\NameGenerationException;
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
    public static function fileDataProvider(): array
    {
        $entity = new DummyEntity();
        $entity->someProperty = 'foo';

        $weirdEntity = new DummyEntity();
        $weirdEntity->someProperty = 'Yéô';

        return [
            'with ext' => ['some-file-name.jpeg',    'foo.jpeg',                 $entity,      'someProperty',     false],
            'without ext' => ['some-file-name',      'foo',                      $entity,      'someProperty',     false],
            'method call' => ['some-file-name.jpeg', 'generated-file-name.jpeg', $entity,      'generateFileName', false],
            'translit.' => ['some-file-name.jpeg',   'yeo.jpeg',                 $weirdEntity, 'someProperty',     true],
        ];
    }

    /**
     * @dataProvider fileDataProvider
     */
    public function testNameReturnsTheRightName(
        string $originalFileName,
        string $expectedFileName,
        object $entity,
        string $propertyName,
        bool $transliterate
    ): void {
        $file = $this->getUploadedFileMock();
        $file
            ->method('getClientOriginalName')
            ->willReturn($originalFileName);

        $mapping = $this->getPropertyMappingMock();
        $mapping->expects(self::once())
            ->method('getFile')
            ->with($entity)
            ->willReturn($file);

        $namer = new PropertyNamer($this->getTransliterator());
        $namer->configure(['property' => $propertyName, 'transliterate' => $transliterate]);

        self::assertSame($expectedFileName, $namer->name($entity, $mapping));
    }

    public function testNameFailsIfThePropertyDoesNotExist(): void
    {
        $this->expectException(NameGenerationException::class);

        $entity = new DummyEntity();
        $mapping = $this->getPropertyMappingMock();

        $namer = new PropertyNamer($this->getTransliterator());
        $namer->configure(['property' => 'nonExistentProperty']);

        $namer->name($entity, $mapping);
    }

    public function testNameFailsIfThePropertyIsEmpty(): void
    {
        $this->expectException(NameGenerationException::class);

        $mapping = $this->getPropertyMappingMock();
        $namer = new PropertyNamer($this->getTransliterator());

        $namer->configure(['property' => 'someProperty']);

        $namer->name(new DummyEntity(), $mapping);
    }

    public function testNamerNeedsToBeConfigured(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The property to use can not be determined. Did you call the configure() method?');

        $mapping = $this->getPropertyMappingMock();
        $namer = new PropertyNamer($this->getTransliterator());

        $namer->name(new DummyEntity(), $mapping);
    }

    public function testConfigurationFailsIfThePropertyIsntSpecified(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Option "property" is missing or empty.');

        $namer = new PropertyNamer($this->getTransliterator());

        $namer->configure(['incorrect' => 'options']);
    }
}
