<?php

namespace Vich\UploaderBundle\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

class TestCase extends BaseTestCase
{
    /**
     * @return UploadedFile
     */
    protected function getUploadedFileMock()
    {
        return $this->getMockBuilder(UploadedFile::class)
            ->setConstructorArgs(['lala', 'lala', $mimeType = null, $size = null, $error = 9, $test = true])
            ->getMock();
    }

    /**
     * @return PropertyMapping
     */
    protected function getPropertyMappingMock()
    {
        return $this->getMockBuilder(PropertyMapping::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return PropertyMappingFactory
     */
    protected function getPropertyMappingFactoryMock()
    {
        return $this->getMockBuilder(PropertyMappingFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
