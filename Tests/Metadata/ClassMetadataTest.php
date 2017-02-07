<?php

namespace Vich\UploaderBundle\Tests\Metadata;

use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Metadata\ClassMetadata;

class ClassMetadataTest extends TestCase
{
    public function testFieldsAreSerialized()
    {
        $fields = ['foo', 'bar', 'baz'];
        $metadata = new ClassMetadata('DateTime');
        $metadata->fields = $fields;

        $deserializedMetadata = unserialize(serialize($metadata));

        $this->assertSame($fields, $deserializedMetadata->fields);
    }
}
