<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Exception\NameGenerationException;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Directory namer which can create subfolder depends on property.
 *
 * @author Raynald Coupé <raynald@easi-services.fr>
 */
class SubdirDirectoryPropertyNamer implements DirectoryNamerInterface, ConfigurableInterface
{
    /**
     * @var string
     */
    private $propertyPath;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @param PropertyAccessorInterface $propertyAccessor Property accessor interface
     */
    public function __construct(PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param array $options Options for this namer. The following options are accepted:
     *                       - property: path to the property used to name the file. Can be either an attribute or a method.
     */
    public function configure(array $options)
    {
        if (empty($options['property'])) {
            throw new \InvalidArgumentException('Option "property" is missing or empty.');
        }

        $this->propertyPath = $options['property'];
    }

    /**
     * {@inheritdoc}
     */
    public function directoryName($object, PropertyMapping $mapping)
    {
        if (empty($this->propertyPath)) {
            throw new \LogicException('The property to use can not be determined. Did you call the configure() method?');
        }

        try {
            $name = $this->propertyAccessor->getValue($object, $this->propertyPath);
        } catch (NoSuchPropertyException $e) {
            throw new NameGenerationException(sprintf('Directory name could not be generated: property %s does not exist.', $this->propertyPath), $e->getCode(), $e);
        }

        if (empty($name)) {
            throw new NameGenerationException(sprintf('Directory name could not be generated: property %s is empty.', $this->propertyPath));
        }

        return $name;
    }
}
