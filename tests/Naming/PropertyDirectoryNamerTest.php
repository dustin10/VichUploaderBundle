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
    public static function fileDataProvider(): array
    {
        $entity = new DummyEntity();
        $entity->someProperty = 'foo';

        $weirdEntity = new DummyEntity();
        $weirdEntity->someProperty = 'Yéô';

        return [
            'plain' => ['foo',                       $entity,      'someProperty',     false],
            'method call' => ['generated-file-name', $entity,      'generateFileName', false],
            'translit.' => ['yeo',                   $weirdEntity, 'someProperty',     true],
        ];
    }

    /**
     * @dataProvider fileDataProvider
     */
    public function testNameReturnsTheRightName(
        string $expectedDirectoryName,
        object $entity,
        string $propertyName,
        bool $transliterate
    ): void {
        $mapping = $this->getPropertyMappingMock();

        $namer = new PropertyDirectoryNamer(null, $this->getTransliterator());
        $namer->configure(['property' => $propertyName, 'transliterate' => $transliterate]);

        self::assertSame($expectedDirectoryName, $namer->directoryName($entity, $mapping));
    }

    public function testNameFailsIfThePropertyDoesNotExist(): void
    {
        $this->expectException(\Vich\UploaderBundle\Exception\NameGenerationException::class);

        $entity = new DummyEntity();
        $mapping = $this->getPropertyMappingMock();

        $namer = new PropertyDirectoryNamer(null, $this->getTransliterator());
        $namer->configure(['property' => 'nonExistentProperty']);

        $namer->directoryName($entity, $mapping);
    }

    public function testNameFailsIfThePropertyIsEmpty(): void
    {
        $this->expectException(\Vich\UploaderBundle\Exception\NameGenerationException::class);

        $mapping = $this->getPropertyMappingMock();
        $namer = new PropertyDirectoryNamer(null, $this->getTransliterator());

        $namer->configure(['property' => 'someProperty']);

        $namer->directoryName(new DummyEntity(), $mapping);
    }

    public function testNamerNeedsToBeConfigured(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The property to use can not be determined. Did you call the configure() method?');

        $mapping = $this->getPropertyMappingMock();
        $namer = new PropertyDirectoryNamer(null, $this->getTransliterator());

        $namer->directoryName(new DummyEntity(), $mapping);
    }

    public function testConfigurationFailsIfThePropertyIsntSpecified(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Option "property" is missing or empty.');

        $namer = new PropertyDirectoryNamer(null, $this->getTransliterator());

        $namer->configure(['incorrect' => 'options']);
    }
}
