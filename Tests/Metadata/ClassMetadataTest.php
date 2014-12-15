<?php

namespace Vich\UploaderBundle\Tests\Metadata;

use Vich\UploaderBundle\Metadata\ClassMetadata;

class ClassMetadataTest extends \PHPUnit_Framework_TestCase
{
    public function testFieldsAreSerialized()
    {
        $fields = array('foo', 'bar', 'baz');
        $metadata = new ClassMetadata('DateTime');
        $metadata->fields = $fields;

        $deserializedMetadata = unserialize(serialize($metadata));

        $this->assertSame($fields, $deserializedMetadata->fields);
    }
}
