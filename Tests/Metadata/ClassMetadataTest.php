<?php

namespace Vich\UploaderBundle\Tests\Metadata;

use Metadata\ClassMetadata;
use PHPUnit\Framework\TestCase;

class ClassMetadataTest extends TestCase
{
    public function testFieldsAreSerialized(): void
    {
        $fields = ['foo', 'bar', 'baz'];
        $metadata = new ClassMetadata('DateTime');
        $metadata->fields = $fields;

        $deserializedMetadata = unserialize(serialize($metadata));

        $this->assertSame($fields, $deserializedMetadata->fields);
    }
}
