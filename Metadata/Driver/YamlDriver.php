<?php

namespace Vich\UploaderBundle\Metadata\Driver;

use Metadata\Driver\AbstractFileDriver;
use Symfony\Component\Yaml\Yaml as YmlParser;

use Vich\UploaderBundle\Metadata\ClassMetadata;

/**
 * Yaml driver
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class YamlDriver extends AbstractFileDriver
{
    /**
     * {@inheritDoc}
     */
    protected function loadMetadataFromFile(\ReflectionClass $class, $file)
    {
        $config = $this->loadMappingFile($file);

        if (!isset($config[$class->name])) {
            throw new \RuntimeException(sprintf('Expected metadata for class %s to be defined in %s.', $class->name, $file));
        }

        $metadata = new ClassMetadata($class->name);

        foreach ($config[$class->name] as $field => $mappingData) {
            $fieldMetadata = array(
                'mapping'           => $mappingData['mapping'],
                'propertyName'      => $field,
                'fileNameProperty'  => $mappingData['filename_property'],
            );

            $metadata->fields[$field] = $fieldMetadata;
        }

        return $metadata;
    }

    protected function loadMappingFile($file)
    {
        return YmlParser::parse($file);
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtension()
    {
        return 'yml';
    }
}
