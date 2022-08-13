<?php

namespace Vich\UploaderBundle\Tests\Metadata;

use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Metadata\ClassMetadata;

class ClassMetadataTest extends TestCase
{
    public function testFieldsAreSerialized(): void
    {
        $fields = ['foo', 'bar', 'baz'];
        $metadata = new ClassMetadata(\DateTime::class);
        $metadata->fields = $fields;

        $deserializedMetadata = \unserialize(\serialize($metadata));

        self::assertSame($fields, $deserializedMetadata->fields);
    }
}
