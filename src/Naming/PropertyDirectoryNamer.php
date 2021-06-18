<?php

namespace Vich\UploaderBundle\Naming;

use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Vich\UploaderBundle\Exception\NameGenerationException;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Util\Transliterator;

/**
 * Directory namer which can create subfolder depends on property.
 *
 * @author Raynald Coupé <raynald@easi-services.fr>
 * @final
 */
class PropertyDirectoryNamer implements DirectoryNamerInterface, ConfigurableInterface
{
    /**
     * @var string
     */
    private $propertyPath;

    /**
     * @var bool
     */
    private $transliterate = false;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var Transliterator
     */
    private $transliterator;

    public function __construct(?PropertyAccessorInterface $propertyAccessor, Transliterator $transliterator)
    {
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
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

    public function directoryName($object, PropertyMapping $mapping): string
    {
        if (empty($this->propertyPath)) {
            throw new \LogicException('The property to use can not be determined. Did you call the configure() method?');
        }

        try {
            $name = $this->propertyAccessor->getValue($object, $this->propertyPath);
        } catch (NoSuchPropertyException $e) {
            throw new NameGenerationException(\sprintf('Directory name could not be generated: property %s does not exist.', $this->propertyPath), $e->getCode(), $e);
        }

        if (empty($name)) {
            throw new NameGenerationException(\sprintf('Directory name could not be generated: property %s is empty.', $this->propertyPath));
        }

        if ($this->transliterate) {
            $name = $this->transliterator->transliterate($name);
        }

        return $name;
    }
}
