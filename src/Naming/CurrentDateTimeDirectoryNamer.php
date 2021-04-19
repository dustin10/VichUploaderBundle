<?php

namespace Vich\UploaderBundle\Naming;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * Directory namer wich can create subfolder depends on current datetime.
 *
 * @author Vyacheslav Startsev <vyacheslav.startsev@gmail.com>
 */
class CurrentDateTimeDirectoryNamer implements DirectoryNamerInterface, ConfigurableInterface
{
    /**
     * @var DateTimeHelper
     */
    private $dateTimeHelper;

    /**
     * @var string
     */
    private $dateTimeFormat = 'Y/m/d';

    /**
     * @var PropertyAccessorInterface|null
     */
    private $propertyAccessor;

    /**
     * @var string|null
     */
    private $dateTimeProperty;

    public function __construct(DateTimeHelper $dateTimeHelper, ?PropertyAccessorInterface $propertyAccessor)
    {
        $this->dateTimeHelper = $dateTimeHelper;
        $this->propertyAccessor = $propertyAccessor;
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

    public function directoryName($object, PropertyMapping $mapping): string
    {
        if (empty($this->dateTimeFormat)) {
            throw new \LogicException('Option "date_time_format" is empty.');
        }
        if (null !== $this->dateTimeProperty) {
            $dateTime = $this->propertyAccessor->getValue($object, $this->dateTimeProperty)->format('U');
        } else {
            // see https://github.com/dustin10/VichUploaderBundle/issues/992
            $msg = 'Not passing "date_time_property" option is deprecated and will be removed in version 2.';
            @\trigger_error($msg, \E_USER_DEPRECATED);
            $dateTime = $this->dateTimeHelper->getTimestamp();
        }

        return \date($this->dateTimeFormat, $dateTime);
    }
}
