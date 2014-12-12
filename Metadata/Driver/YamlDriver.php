<?php

namespace Vich\UploaderBundle\Metadata\Driver;

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
    protected function loadMetadataFromFile($file, \ReflectionClass $class = null)
    {
        $config = $this->loadMappingFile($file);
        $className = $this->guessClassName($file, $config, $class);

        if (!isset($config[$className])) {
            throw new \RuntimeException(sprintf('Expected metadata for class %s to be defined in %s.', $class->name, $file));
        }

        $metadata = new ClassMetadata($className);

        foreach ($config[$className] as $field => $mappingData) {
            $fieldMetadata = array(
                'mapping'           => $mappingData['mapping'],
                'propertyName'      => $field,
                'fileNameProperty'  => isset($mappingData['filename_property']) ? $mappingData['filename_property'] : null,
            );

            $metadata->fields[$field] = $fieldMetadata;
        }

        return $metadata;
    }

    protected function getClassNameFromFile($file)
    {
        return $this->guessClassName($file, $this->loadMappingFile($file));
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

    protected function guessClassName($file, array $config, \ReflectionClass $class = null)
    {
        if ($class === null) {
            return current(array_keys($config));
        }

        if (!isset($config[$class->name])) {
            throw new \RuntimeException(sprintf('Expected metadata for class %s to be defined in %s.', $class->name, $file));
        }

        return $class->name;
    }
}
