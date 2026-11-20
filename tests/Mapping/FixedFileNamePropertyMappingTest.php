<?php

namespace Vich\UploaderBundle\Tests\Mapping;

use PHPUnit\Framework\Attributes\Test;
use Vich\UploaderBundle\Mapping\FixedFileNamePropertyMapping;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

final class FixedFileNamePropertyMappingTest extends TestCase
{
    #[Test]
    public function fileNameIsTheFixedOne(): void
    {
        $object = new DummyEntity();
        $mapping = $this->getPropertyMappingStub();
        $mapping->method('getFileName')->willReturn('new.png');
        $mapping->method('readProperty')->willReturn('new.png');
        $fixedMapping = new FixedFileNamePropertyMapping($mapping, 'old.png');

        $fileName = $fixedMapping->getFileName($object);
        $nameProperty = $fixedMapping->readProperty($object, 'name');

        self::assertSame('old.png', $fileName);
        self::assertSame('old.png', $nameProperty);
    }

    #[Test]
    public function otherPropertiesAreReadFromTheMapping(): void
    {
        $object = new DummyEntity();
        $mapping = $this->getPropertyMappingStub();
        $mapping->method('readProperty')->willReturnMap([[$object, 'size', 42]]);
        $mapping->method('getUploadDir')->willReturn('dir');
        $fixedMapping = new FixedFileNamePropertyMapping($mapping, 'old.png');

        $size = $fixedMapping->readProperty($object, 'size');
        $uploadDir = $fixedMapping->getUploadDir($object);

        self::assertSame(42, $size);
        self::assertSame('dir', $uploadDir);
    }
}
