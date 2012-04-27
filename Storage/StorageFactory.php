<?php

namespace Vich\UploaderBundle\Storage;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * StorageFactory
 *
 * @author ftassi
 */
class StorageFactory
{

    /**
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     *
     * @param ContainerInterface $container 
     */
    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function createStorage()
    {
        return $this->container->get($this->container->getParameter('vich_uploader.storage_service'));
    }

}
