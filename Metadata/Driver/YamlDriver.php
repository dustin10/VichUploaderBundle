<?php

namespace Vich\UploaderBundle\Metadata\Driver;

use Metadata\Driver\AbstractFileDriver;
use Symfony\Component\Yaml\Yaml as YmlParser;
use Vich\UploaderBundle\Metadata\ClassMetadata;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 */
class YamlDriver extends AbstractFileDriver
{
    /**
     * {@inheritdoc}
     */
    protected function loadMetadataFromFile(\ReflectionClass $class, $file)
    {
        $config = $this->loadMappingFile($file);
        $className = $this->guessClassName($file, $config, $class);
        $classMetadata = new ClassMetadata($className);
        $classMetadata->fileResources[] = $file;
        $classMetadata->fileResources[] = $class->getFileName();

        foreach ($config[$className] as $field => $mappingData) {
            $fieldMetadata = [
                'mapping' => $mappingData['mapping'],
                'propertyName' => $field,
                'fileNameProperty' => isset($mappingData['filename_property']) ? $mappingData['filename_property'] : null,
                'size' => isset($mappingData['size']) ? $mappingData['size'] : null,
                'mimeType' => isset($mappingData['mime_type']) ? $mappingData['mime_type'] : null,
                'originalName' => isset($mappingData['original_name']) ? $mappingData['original_name'] : null,
            ];

            $classMetadata->fields[$field] = $fieldMetadata;
        }

        return $classMetadata;
    }

    protected function loadMappingFile($file)
    {
        return YmlParser::parse(file_get_contents($file));
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtension()
    {
        return 'yml';
    }

    protected function guessClassName($file, array $config, \ReflectionClass $class = null)
    {
        if (null === $class) {
            return current(array_keys($config));
        }

        if (!isset($config[$class->name])) {
            throw new \RuntimeException(sprintf('Expected metadata for class %s to be defined in %s.', $class->name, $file));
        }

        return $class->name;
    }
}
