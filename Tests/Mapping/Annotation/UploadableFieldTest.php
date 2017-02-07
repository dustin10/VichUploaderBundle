<?php

namespace Vich\UploaderBundle\Tests\Mapping\Annotation;

use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;

/**
 * UploadableFieldTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class UploadableFieldTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionThrownWhenNoMappingAttribute()
    {
        new UploadableField([
            'fileNameProperty' => 'fileName',
        ]);
    }
}
