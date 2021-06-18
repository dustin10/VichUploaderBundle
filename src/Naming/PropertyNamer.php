<?php

namespace Vich\UploaderBundle\Naming;

use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Vich\UploaderBundle\Exception\NameGenerationException;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Util\Transliterator;

/**
 * PropertyNamer.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 * @final
 */
class PropertyNamer implements NamerInterface, ConfigurableInterface
{
    use Polyfill\FileExtensionTrait;

    /**
     * @var string
     */
    private $propertyPath;

    /**
     * @var bool
     */
    private $transliterate = false;

    /**
     * @var Transliterator
     */
    private $transliterator;

    public function __construct(Transliterator $transliterator)
    {
        $this->transliterator = $transliterator;
    }

    /**
     * @param array $options Options for this namer. The following options are accepted:
     *                       - property: path to the property used to name the file. Can be either an attribute or a method.
     *                       - transliterate: whether the filename should be transliterated or not
     *
     * @throws \InvalidArgumentException
     */
    public function configure(array $options): void
    {
        if (empty($options['property'])) {
            throw new \InvalidArgumentException('Option "property" is missing or empty.');
        }

        $this->propertyPath = $options['property'];
        $this->transliterate = isset($options['transliterate']) ? (bool) $options['transliterate'] : $this->transliterate;
    }

    public function name($object, PropertyMapping $mapping): string
    {
        if (empty($this->propertyPath)) {
            throw new \LogicException('The property to use can not be determined. Did you call the configure() method?');
        }

        $file = $mapping->getFile($object);

        try {
            $name = $this->getPropertyValue($object, $this->propertyPath);
        } catch (NoSuchPropertyException $e) {
            throw new NameGenerationException(\sprintf('File name could not be generated: property %s does not exist.', $this->propertyPath), $e->getCode(), $e);
        }

        if (empty($name)) {
            throw new NameGenerationException(\sprintf('File name could not be generated: property %s is empty.', $this->propertyPath));
        }

        if ($this->transliterate) {
            $name = $this->transliterator->transliterate($name);
        }

        // append the file extension if there is one
        if ($extension = $this->getExtension($file)) {
            $name = \sprintf('%s.%s', $name, $extension);
        }

        return $name;
    }

    /**
     * @return mixed|null
     */
    private function getPropertyValue(object $object, string $propertyPath)
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        return $accessor->getValue($object, $propertyPath);
    }
}
