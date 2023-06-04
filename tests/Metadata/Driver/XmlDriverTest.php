<?php

namespace Vich\UploaderBundle\Tests\Metadata\Driver;

use Metadata\Driver\DriverInterface;
use Vich\UploaderBundle\Metadata\Driver\XmlDriver;

class XmlDriverTest extends FileDriverTestCase
{
    protected static function getExtension(): string
    {
        return 'xml';
    }

    protected function getDriver(\ReflectionClass $reflectionClass, ?string $file): DriverInterface
    {
        return new XmlDriver($this->getFileLocatorMock($reflectionClass, $file));
    }
}
