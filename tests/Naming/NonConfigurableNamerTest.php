<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Vich\UploaderBundle\Tests\Naming\Fixtures\SimpleNamer;
use Vich\UploaderBundle\Tests\TestCase;

final class NonConfigurableNamerTest extends TestCase
{
    public function testNonConfigurableNamerIgnoresKeepExtensionOption(): void
    {
        $namer = new SimpleNamer();
        $file = $this->getUploadedFileMock();
        $file
            ->method('getClientOriginalName')
            ->willReturn('test.xyz')
        ;
        $file
            ->method('guessExtension')
            ->willReturn('txt')
        ;

        $entity = new \stdClass();
        $mapping = $this->getPropertyMappingMock();
        $mapping->expects($this->once())
            ->method('getFile')
            ->with($entity)
            ->willReturn($file)
        ;

        $result = $namer->name($entity, $mapping);

        self::assertEquals('simple_test.txt', $result);
    }
}
