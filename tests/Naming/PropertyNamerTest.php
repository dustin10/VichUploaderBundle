<?php

namespace Vich\UploaderBundle\Tests\Naming;

use PHPUnit\Framework\Attributes\DataProvider;
use Vich\UploaderBundle\Exception\NameGenerationException;
use Vich\UploaderBundle\Naming\PropertyNamer;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

/**
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
            'with ext' => ['some-file-name.jpeg',    'foo.jpg',                 'jpg', $entity,      'someProperty',     false],
            'without ext' => ['some-file-name',      'foo',                      null, $entity,      'someProperty',     false],
            'method call' => ['some-file-name.jpeg', 'generated-file-name.jpg', 'jpg', $entity,      'generateFileName', false],
            'translit.' => ['some-file-name.jpeg',   'yeo.jpg',                 'jpg', $weirdEntity, 'someProperty',     true],
        ];
    }

    #[DataProvider('fileDataProvider')]
    public function testNameReturnsTheRightName(
        string $originalFileName,
        string $expectedFileName,
        ?string $guessedExtension,
        object $entity,
        string $propertyName,
        bool $transliterate
    ): void {
        $file = $this->getUploadedFileMock();
        $file
            ->method('getClientOriginalName')
            ->willReturn($originalFileName);

        $file
            ->method('guessExtension')
            ->willReturn($guessedExtension);

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
