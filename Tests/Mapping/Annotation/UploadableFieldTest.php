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
    public function testExceptionThrownWhenNoMappingAttribute(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new UploadableField([
            'fileNameProperty' => 'fileName',
        ]);
    }
}
