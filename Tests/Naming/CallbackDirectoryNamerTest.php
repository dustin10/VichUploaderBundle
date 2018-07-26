<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\CallableDirectoryNameProviderInterface;
use Vich\UploaderBundle\Naming\CallableNameProviderInterface;
use Vich\UploaderBundle\Naming\CallbackDirectoryNamer;
use Vich\UploaderBundle\Naming\CallbackNamer;
use Vich\UploaderBundle\Tests\TestCase;

class CallbackDirectoryNamerTest extends TestCase
{
    /**
     * @expectedException \Vich\UploaderBundle\Exception\NameGenerationException
     * @expectedExceptionMessage Object "stdClass" must implement the "Vich\UploaderBundle\Naming\CallableDirectoryNameProviderInterface" interface to use the directory namer "Vich\UploaderBundle\Naming\CallbackDirectoryNamer".
     */
    public function testCallbackException(): void
    {
        $mapping = $this->getMockBuilder(PropertyMapping::class)
            ->disableOriginalConstructor()
            ->getMock();

        $namer = new CallbackDirectoryNamer();
        $namer->directoryName(new \stdClass(), $mapping);
    }

    public function testCallback(): void
    {
        $object = $this->getMockBuilder(CallableDirectoryNameProviderInterface::class)
            ->getMock();
        $object->expects($this->once())
            ->method('getUploadedDirectoryName')
            ->will($this->returnValue('my_dir_name'));

        $mapping = $this->getMockBuilder(PropertyMapping::class)
            ->disableOriginalConstructor()
            ->getMock();

        $namer = new CallbackDirectoryNamer();
        $this->assertEquals('my_dir_name', $namer->directoryName($object, $mapping));
    }
}
