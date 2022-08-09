<?php

namespace Vich\UploaderBundle\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Util\Transliterator;

class TestCase extends BaseTestCase
{
    /**
     * @return UploadedFile&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getUploadedFileMock(): UploadedFile
    {
        return $this->getMockBuilder(UploadedFile::class)
            ->setConstructorArgs(['lala', 'lala', $mimeType = null, $error = 9, $test = true])
            ->getMock();
    }

    /**
     * @return ReplacingFile&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getReplacingFileMock(): ReplacingFile
    {
        return $this->getMockBuilder(ReplacingFile::class)
            ->setConstructorArgs(['lala', false])
            ->getMock();
    }

    /**
     * @return PropertyMapping&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getPropertyMappingMock(): PropertyMapping
    {
        return $this->getMockBuilder(PropertyMapping::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return PropertyMappingFactory&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getPropertyMappingFactoryMock(): PropertyMappingFactory
    {
        return $this->getMockBuilder(PropertyMappingFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getTransliterator(): Transliterator
    {
        return new Transliterator(new AsciiSlugger());
    }
}
