<?php

namespace Vich\UploaderBundle\Mapping;

/**
 * Cached PropertyMappingFactory
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class CachedPropertyMappingFactory extends PropertyMappingFactory
{
    protected $cache = array();

    public function fromObject($obj, $className = null)
    {
        $class = $this->getClassName($obj, $className);

        if (isset($this->cache[$class])) {
            return $this->cache[$class];
        }

        return $this->cache[$class] = parent::fromObject($obj, $className);
    }

    public function fromField($obj, $field, $className = null)
    {
        $class = $this->getClassName($obj, $className);

        if (isset($this->cache[$class.'::'.$field])) {
            return $this->cache[$class.'::'.$field];
        }

        return $this->cache[$class.'::'.$field] = parent::fromField($obj, $field, $className);;
    }
}
