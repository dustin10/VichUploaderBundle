<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\CallableNameProviderInterface;
use Vich\UploaderBundle\Naming\CallbackNamer;
use Vich\UploaderBundle\Tests\TestCase;

class CallbackNamerTest extends TestCase
{
    /**
     * @expectedException \Vich\UploaderBundle\Exception\NameGenerationException
     * @expectedExceptionMessage Object "stdClass" must implement the "Vich\UploaderBundle\Naming\CallableNameProviderInterface" interface to use the namer "Vich\UploaderBundle\Naming\CallbackNamer".
     */
    public function testCallbackException(): void
    {
        $mapping = $this->getMockBuilder(PropertyMapping::class)
            ->disableOriginalConstructor()
            ->getMock();

        $namer = new CallbackNamer();
        $namer->name(new \stdClass(), $mapping);
    }

    public function testCallback(): void
    {
        $object = $this->getMockBuilder(CallableNameProviderInterface::class)
            ->getMock();
        $object->expects($this->once())
            ->method('getUploadedFileName')
            ->will($this->returnValue('my_name'));

        $mapping = $this->getMockBuilder(PropertyMapping::class)
            ->disableOriginalConstructor()
            ->getMock();

        $namer = new CallbackNamer();
        $this->assertEquals('my_name', $namer->name($object, $mapping));
    }
}
