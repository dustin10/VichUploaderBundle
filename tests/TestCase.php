<?php

namespace Vich\UploaderBundle\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;
use Vich\UploaderBundle\Handler\UploadHandlerInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingFactoryInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingInterface;
use Vich\UploaderBundle\Metadata\MetadataReaderInterface;
use Vich\UploaderBundle\Util\Transliterator;

abstract class TestCase extends BaseTestCase
{
    protected function getUploadedFileMock(): UploadedFile|MockObject
    {
        return $this->getMockBuilder(UploadedFile::class)
            ->setConstructorArgs(['lala', 'lala', null, 9, true])
            ->getMock();
    }

    protected function getReplacingFileMock(): ReplacingFile|MockObject
    {
        return $this->getMockBuilder(ReplacingFile::class)
            ->setConstructorArgs(['lala', false])
            ->getMock();
    }

    protected function getPropertyMappingMock(): PropertyMappingInterface|MockObject
    {
        return $this->createMock(PropertyMappingInterface::class);
    }

    protected function getPropertyMappingFactoryMock(): PropertyMappingFactoryInterface|MockObject
    {
        return $this->createMock(PropertyMappingFactoryInterface::class);
    }

    protected function getTransliterator(): Transliterator
    {
        return new Transliterator(new AsciiSlugger());
    }

    protected function getMetadataReaderMock(): MetadataReaderInterface|MockObject
    {
        return $this->createMock(MetadataReaderInterface::class);
    }

    protected function getUploadHandlerMock(): UploadHandlerInterface|MockObject
    {
        return $this->createMock(UploadHandlerInterface::class);
    }
}
