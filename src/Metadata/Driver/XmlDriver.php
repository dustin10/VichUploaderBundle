<?php

namespace Vich\UploaderBundle\Metadata\Driver;

use Metadata\ClassMetadata as JMSClassMetadata;
use Metadata\Driver\AbstractFileDriver;
use Symfony\Component\Config\Util\XmlUtils;
use Vich\UploaderBundle\Metadata\ClassMetadata;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 */
class XmlDriver extends AbstractFileDriver
{
    protected function loadMetadataFromFile(\ReflectionClass $class, string $file): ?JMSClassMetadata
    {
        $elem = XmlUtils::loadFile($file);
        $elem = \simplexml_import_dom($elem);

        $className = $this->guessClassName($file, $elem, $class);
        $classMetadata = new ClassMetadata($className);
        $classMetadata->fileResources[] = $file;
        $classMetadata->fileResources[] = $class->getFileName();

        foreach ($elem->children() as $field) {
            $fieldMetadata = [
                'mapping' => (string) $field->attributes()->mapping,
                'propertyName' => (string) $field->attributes()->name,
                'fileNameProperty' => (string) $field->attributes()->filename_property,
                'size' => (string) $field->attributes()->size,
                'mimeType' => (string) $field->attributes()->mime_type,
                'originalName' => (string) $field->attributes()->original_name,
                'dimensions' => null === $field->attributes()->dimensions ? null : (string) $field->attributes()->dimensions,
            ];

            $classMetadata->fields[(string) $field->attributes()->name] = $fieldMetadata;
        }

        return $classMetadata;
    }

    protected function getExtension(): string
    {
        return 'xml';
    }

    protected function guessClassName(string $file, \SimpleXMLElement $elem, \ReflectionClass $class = null): string
    {
        if (null === $class) {
            return (string) $elem->attributes()->class;
        }

        if ($class->name !== (string) $elem->attributes()->class) {
            throw new \RuntimeException(\sprintf('Expected metadata for class %s to be defined in %s.', $class->name, $file));
        }

        return $class->name;
    }
}
