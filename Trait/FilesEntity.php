<?php

namespace Vich\UploaderBundle\Trait;

use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;
use Doctrine\ORM\Mapping as ORM;

trait FilesEntity
{
    /** @ORM\Column(type="string", nullable=true) */
    protected $VichFilesBug = '';

    private function getVichUploaderStorage()
    {
        global $kernel;
        $container = $kernel->getContainer();
        return $container->get('vich_uploader.storage');
    }

    public function __set($attr, $value)
    {
        $ref = new \ReflectionObject($this);
        if ($this->endsWith($attr, 'Del'))
            $base = substr($attr, 0, -3);
        elseif ($this->endsWith($attr, 'Name'))
            $base = substr($attr, 0, -4);
        elseif ($ref->hasProperty($attr.'Name'))
            $base = $attr;
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        $annotation = $reader->getPropertyAnnotation($ref->getProperty($base), 'Vich\UploaderBundle\Mapping\Annotation\UploadableField');
        if (!$annotation)
            throw new NoSuchPropertyException();
        /** @var $annotation UploadableField */
        if ($this->endsWith($attr, 'Del'))
        {
            if ($value)
            {
                $name = $base.'Name';
                if ($this->$name)
                {
                    $path = $this->getVichUploaderStorage()->resolvePath($this, $base);
                    if (is_file($path))
                        unlink($path);
                    $this->$name = null;
                }
            }
        } elseif ($this->endsWith($attr, 'Name')) {
            $this->$attr = $value;
        } elseif ($ref->hasProperty($attr.'Name')) {
            if ($attr === false)
            {
                $name = $base.'Name';
                $path = $this->getVichUploaderStorage()->resolvePath($this, $base);
                if (is_file($path))
                    unlink($path);
                $this->$name = null;

            }
            if ($attr !== null)
            {
                $this->VichFilesBug = uniqid();
                $this->$attr = $value;
            }
        }
    }

    public function __get($attr)
    {
        $ref = new \ReflectionObject($this);
        if ($this->endsWith($attr, 'Del'))
            return false;
        elseif ($this->endsWith($attr, 'Name'))
            return $this->$attr;
        elseif ($ref->hasProperty($attr.'Name'))
            return $this->$attr;
        throw new NoSuchPropertyException();
    }

    private function endsWith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

} 
