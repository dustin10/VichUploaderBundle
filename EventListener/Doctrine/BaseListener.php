<?php

namespace Vich\UploaderBundle\EventListener\Doctrine;

use Doctrine\Common\EventSubscriber;
use Vich\UploaderBundle\Adapter\DoctrineAdapter;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Util\ClassUtils;

/**
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
abstract class BaseListener implements EventSubscriber
{
    /**
     * @var array[]
     */
    protected $mappings;

    protected $adapter;

    protected $metadata;

    protected $handler;

    public function __construct($mappings, DoctrineAdapter $adapter, MetadataReader $metadata, UploadHandler $handler)
    {
        $this->mappings = $mappings;
        $this->adapter = $adapter;
        $this->metadata = $metadata;
        $this->handler = $handler;
    }

    protected function isFlagEnabledForField($flag, array $field)
    {
        return true === $this->mappings[$field['mapping']][$flag];
    }

    protected function getUploadableFields($object)
    {
        return $this->metadata->getUploadableFields(ClassUtils::getClass($object));
    }
}
