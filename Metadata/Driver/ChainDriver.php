<?php

namespace Vich\UploaderBundle\Metadata\Driver;

use Metadata\Driver\AdvancedDriverInterface;
use Metadata\Driver\DriverInterface;

class ChainDriver implements AdvancedDriverInterface
{
    protected $drivers;

    public function __construct(array $drivers = [])
    {
        $this->drivers = $drivers;
    }

    public function addDriver(DriverInterface $driver)
    {
        $this->drivers[] = $driver;
    }

    public function loadMetadataForClass(\ReflectionClass $class)
    {
        foreach ($this->drivers as $driver) {
            if (null !== ($metadata = $driver->loadMetadataForClass($class))) {
                return $metadata;
            }
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllClassNames()
    {
        $classes = [];
        foreach ($this->drivers as $driver) {
            if (!$driver instanceof AdvancedDriverInterface) {
                continue;
            }

            $driverClasses = $driver->getAllClassNames();
            if (!empty($driverClasses)) {
                $classes = array_merge($classes, $driverClasses);
            }
        }

        return $classes;
    }
}
