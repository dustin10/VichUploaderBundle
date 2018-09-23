<?php

namespace Vich\UploaderBundle\Naming;

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
     * CurrentDateTimeDirectoryNamer constructor.
     *
     * @param DateTimeHelper $dateTimeHelper
     */
    public function __construct(DateTimeHelper $dateTimeHelper)
    {
        $this->dateTimeHelper = $dateTimeHelper;
    }

    /**
     * @param array $options Options for this namer.
     *
     * Only one option accepted:
     *     - date_time_format: The format of the outputted date. See: http://php.net/manual/en/function.date.php
     */
    public function configure(array $options): void
    {
        $options = array_merge(['date_time_format' => $this->dateTimeFormat], $options);

        $this->dateTimeFormat = $options['date_time_format'];
    }

    /**
     * {@inheritdoc}
     */
    public function directoryName($object, PropertyMapping $mapping): string
    {
        if (empty($this->dateTimeFormat)) {
            throw new \LogicException('Option "date_time_format" is empty.');
        }

        return date($this->dateTimeFormat, $this->dateTimeHelper->getTimestamp());
    }
}
