<?php

namespace Vich\UploaderBundle\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

class TestCase extends BaseTestCase
{
    protected function getUploadedFileMock(): UploadedFile
    {
        return $this->getMockBuilder(UploadedFile::class)
            ->setConstructorArgs(['lala', 'lala', $mimeType = null, $error = 9, $test = true])
            ->getMock();
    }

    protected function getPropertyMappingMock(): PropertyMapping
    {
        return $this->getMockBuilder(PropertyMapping::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getPropertyMappingFactoryMock(): PropertyMappingFactory
    {
        return $this->getMockBuilder(PropertyMappingFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
