<?php

namespace Vich\UploaderBundle\Metadata\Driver;

use Vich\UploaderBundle\Metadata\ClassMetadata;

/**
 * Xml driver
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class XmlDriver extends AbstractFileDriver
{
    protected function loadMetadataFromFile($file, \ReflectionClass $class = null)
    {
        $previous = libxml_use_internal_errors(true);
        $elem = simplexml_load_file($file);
        libxml_use_internal_errors($previous);

        if (false === $elem) {
            throw new \RuntimeException(libxml_get_last_error());
        }

        $className = $this->guessClassName($file, $elem, $class);
        $metadata = new ClassMetadata($className);

        foreach ($elem->children() as $field) {
            $fieldMetadata = array(
                'mapping'           => (string) $field->attributes()->mapping,
                'propertyName'      => (string) $field->attributes()->name,
                'fileNameProperty'  => (string) $field->attributes()->filename_property,
            );

            $metadata->fields[(string) $field->attributes()->name] = $fieldMetadata;
        }

        return $metadata;
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtension()
    {
        return 'xml';
    }

    protected function guessClassName($file, \SimpleXMLElement $elem, \ReflectionClass $class = null)
    {
        if ($class === null) {
            return (string) $elem->attributes()->class;
        }

        if ($class->name !== (string) $elem->attributes()->class) {
            throw new \RuntimeException(sprintf('Expected metadata for class %s to be defined in %s.', $class->name, $file));
        }

        return $class->name;
    }
}
