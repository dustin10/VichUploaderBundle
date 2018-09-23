<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * Directory namer wich can create subfolder depends on current date.
 *
 * @author Vyacheslav Startsev <vyacheslav.startsev@gmail.com>
 */
class CurrentDateDirectoryNamer implements DirectoryNamerInterface, ConfigurableInterface
{
    /**
     * @var string
     */
    private $dateFormat = 'Y/m/d';

    /**
     * @param array $options Options for this namer.
     *
     * Only one option accepted:
     *     - date_format: The format of the outputted date. See: http://php.net/manual/en/function.date.php
     */
    public function configure(array $options): void
    {
        $options = array_merge(['date_format' => $this->dateFormat], $options);

        $this->dateFormat = $options['date_format'];
    }

    /**
     * {@inheritdoc}
     */
    public function directoryName($object, PropertyMapping $mapping): string
    {
        if (empty($this->dateFormat)) {
            throw new \LogicException('The date format to use can not be determined.');
        }

        return date($this->dateFormat);
    }
}
