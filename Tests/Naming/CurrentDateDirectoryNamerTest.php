<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Vich\UploaderBundle\Naming\CurrentDateDirectoryNamer;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * CurrentDateDirectoryNamerTest
 *
 * @author David Romaní <david@flux.cat>
 */
class CurrentDateDirectoryNamerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test Current Date Directory Namer
     */
    public function testDirectoryNameReturnsACurrentDate()
    {
        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $entity = new DummyEntity;
        $entity->setFile($file);

        $directoryNamer = new CurrentDateDirectoryNamer();
        $currentDate = new \DateTime();

        $this->assertEquals(
            'uploadDir/' . $currentDate->format('Y/m/d'),
            $directoryNamer->directoryName($entity, 'file', 'uploadDir')
        );
    }
}
