<?php

namespace Vich\UploaderBundle\Naming;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Vich\UploaderBundle\Exception\NameGenerationException;
use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * PropertyNamer
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class PropertyNamer implements NamerInterface
{
    private $propertyPath;

    /**
     * @param string The path to the property used to name the file. Can be
     *               either an attribute or a method.
     */
    public function __construct($propertyPath)
    {
        $this->propertyPath = $propertyPath;
    }

    /**
     * @inheritDoc
     */
    public function name($object, PropertyMapping $mapping)
    {
        $file = $mapping->getFile($object);

        try {
            $name = $this->getPropertyValue($object, $this->propertyPath);
        } catch (NoSuchPropertyException $e) {
            throw new NameGenerationException(sprintf('File name could not be generated: property %s does not exist.', $this->propertyPath));
        }

        if (empty($name)) {
            throw new NameGenerationException(sprintf('File name could not be generated: property %s is empty.', $this->propertyPath));
        }

        // append the file extension if there is one
        if ($extension = $this->getExtension($file)) {
            $name = sprintf('%s.%s', $name, $extension);
        }

        return $name;
    }

    private function getPropertyValue($object, $propertyPath)
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        return $accessor->getValue($object, $propertyPath);
    }

    private function getExtension(UploadedFile $file)
    {
        $originalName = $file->getClientOriginalName();

        if ($extension = pathinfo($originalName, PATHINFO_EXTENSION)) {
            return $extension;
        }

        if ($extension = $file->guessExtension()) {
            return $extension;
        }

        return null;
    }
}
