<?php

namespace Vich\UploaderBundle\Metadata\Driver;

use Metadata\Driver\AbstractFileDriver;

use Vich\UploaderBundle\Metadata\ClassMetadata;

/**
 * Xml driver
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class XmlDriver extends AbstractFileDriver
{
    protected function loadMetadataFromFile(\ReflectionClass $class, $file)
    {
        $previous = libxml_use_internal_errors(true);
        $elem = simplexml_load_file($file);
        libxml_use_internal_errors($previous);

        if (false === $elem) {
            throw new \RuntimeException(libxml_get_last_error());
        }

        $metadata = new ClassMetadata($class->name);

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
}
