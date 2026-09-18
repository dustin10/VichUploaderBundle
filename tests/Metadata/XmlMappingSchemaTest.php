<?php

namespace Vich\UploaderBundle\Tests\Metadata;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Keeps vich_uploader.xsd in step with the mappings the XmlDriver actually reads.
 */
class XmlMappingSchemaTest extends TestCase
{
    private const SCHEMA = __DIR__.'/../../vich_uploader.xsd';

    #[DataProvider('provideMappingFiles')]
    #[Test]
    public function shippedMappingsValidateAgainstTheSchema(string $file): void
    {
        $this->assertValidatesAgainstSchema(\file_get_contents($file), $file);
    }

    public static function provideMappingFiles(): iterable
    {
        foreach (\glob(__DIR__.'/../Fixtures/TestBundle/config/vich_uploader/*.xml') as $file) {
            yield \basename($file) => [$file];
        }
    }

    #[Test]
    public function severalFieldsAreAllowed(): void
    {
        $this->assertValidatesAgainstSchema(<<<'XML'
            <?xml version="1.0" encoding="UTF-8" ?>
            <vich_uploader xmlns="https://vich-uploader-bundle/schema/"
                           class="Vich\TestBundle\Entity\Product">
                <field mapping="dummy_file" name="attachment" filename_property="attachmentName" />
                <field mapping="dummy_image" name="image" filename_property="imageName" />
            </vich_uploader>
            XML);
    }

    #[Test]
    public function theRequiredAttributesAreEnforced(): void
    {
        $previous = \libxml_use_internal_errors(true);

        $document = new \DOMDocument();
        $document->loadXML(<<<'XML'
            <?xml version="1.0" encoding="UTF-8" ?>
            <vich_uploader xmlns="https://vich-uploader-bundle/schema/"
                           class="Vich\TestBundle\Entity\Product">
                <field name="image" />
            </vich_uploader>
            XML);

        $valid = $document->schemaValidate(self::SCHEMA);

        \libxml_clear_errors();
        \libxml_use_internal_errors($previous);

        self::assertFalse($valid, 'A field without "mapping" and "filename_property" should be rejected.');
    }

    private function assertValidatesAgainstSchema(string $xml, ?string $file = null): void
    {
        $previous = \libxml_use_internal_errors(true);

        $document = new \DOMDocument();
        $document->loadXML($xml);

        $valid = $document->schemaValidate(self::SCHEMA);

        $messages = \array_map(
            static fn (\LibXMLError $error): string => \trim($error->message),
            \libxml_get_errors()
        );

        \libxml_clear_errors();
        \libxml_use_internal_errors($previous);

        self::assertTrue($valid, \sprintf("%s does not validate:\n%s", $file ?? 'The document', \implode("\n", $messages)));
    }
}
