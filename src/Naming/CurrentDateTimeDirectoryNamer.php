<?php

namespace Vich\UploaderBundle\Naming;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Util\PropertyPathUtils;

/**
 * Directory namer wich can create subfolder depends on current datetime.
 *
 * @author Vyacheslav Startsev <vyacheslav.startsev@gmail.com>
 */
final class CurrentDateTimeDirectoryNamer implements DirectoryNamerInterface, ConfigurableInterface
{
    private string $dateTimeFormat = 'Y/m/d';

    private ?string $dateTimeProperty = null;

    public function __construct(private readonly ?PropertyAccessorInterface $propertyAccessor)
    {
    }

    /**
     * @param array $options Options for this namer.
     *
     * Options accepted:
     *     - date_time_format: The format of the outputted date. See: http://php.net/manual/en/function.date.php
     *     - date_time_property: The property to get uploading datetime
     */
    public function configure(array $options): void
    {
        $options = \array_merge(['date_time_format' => $this->dateTimeFormat], $options);

        $this->dateTimeFormat = $options['date_time_format'];

        if (isset($options['date_time_property'])) {
            $this->dateTimeProperty = $options['date_time_property'];
        }
    }

    public function directoryName(object|array $object, PropertyMapping $mapping): string
    {
        if (empty($this->dateTimeFormat)) {
            throw new \LogicException('Option "date_time_format" is empty.');
        }
        if (null !== $this->dateTimeProperty) {
            $dateTime = $this->propertyAccessor->getValue(
                $object,
                PropertyPathUtils::fixPropertyPath($object, $this->dateTimeProperty)
            )->format('U');
        } else {
            throw new \LogicException('Option "date_time_property" is mandatory.');
        }

        return \date($this->dateTimeFormat, $dateTime);
    }
}
