<?php

namespace Vich\UploaderBundle\Storage\Adapter;

use Vich\UploaderBundle\Storage\Adapter\CDNAdapterInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Description of RackspaceCloudFilesAdapter
 *
 ** @author Francesco Tassi <tassi.francesco@gmail.com>
 */
class RackspaceCloudFilesAdapter implements CDNAdapterInterface, ContainerAwareInterface
{

    /**
     *
     * @var \CF_Authentication
     */
    protected $rackspaceAuthentication;

    /**
     *
     * @var \CF_Connection
     */
    protected $rackspaceConnection;

    /**
     *
     * @var string
     */
    protected $mediaContainer;

    /**
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     *
     * @var boolean
     */
    protected $authenticated = false;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getMediaContainer()
    {
        return $this->mediaContainer;
    }

    /**
     *
     * @param string $container
     */
    public function setMediaContainer($container)
    {
        $this->mediaContainer = $container;
    }

    /**
     *
     * @return \CF_Connection
     */
    public function getRackspaceConnection()
    {
        return $this->rackspaceConnection;
    }

    /**
     *
     * @param \CF_Connection $rackspaceConnection
     */
    public function setRackspaceConnection(\CF_Connection $rackspaceConnection)
    {
        $this->rackspaceConnection = $rackspaceConnection;
    }

    /**
     *
     * @param  string $filename
     * @return string
     */
    public function getAbsoluteUri($filename)
    {
        $mediaContainer = $this->getAuthenticatedConnection()->get_container($this->mediaContainer);

        try {
            $object = $mediaContainer->get_object($filename);
        } catch (\NoSuchObjectException $exc) {
            return '';
        }

        return $object->public_uri();

    }

    /**
     *
     * @param string $filePath
     * @param string $fileName
     */
    public function put($filePath, $fileName)
    {
        $this->getAuthenticatedConnection();
        $mediaContainer = $this->getAuthenticatedConnection()->get_container($this->mediaContainer);
        $object = $mediaContainer->create_object($fileName);

        return $object->load_from_filename($filePath);
    }

    /**
     *
     * @param  string  $fileName
     * @return boolean
     */
    public function remove($fileName)
    {
        $this->getAuthenticatedConnection();
        $mediaContainer = $this->getAuthenticatedConnection()->get_container($this->mediaContainer);

        return $mediaContainer->delete_object($fileName);
    }

    /**
     *
     * @return \CF_Connection
     */
    protected function getAuthenticatedConnection()
    {
        if (!$this->authenticated) {
            $this->rackspaceAuthentication = $this->container->get('rackspacecloudfiles_authentication');
            $this->rackspaceAuthentication->authenticate();
            $this->rackspaceConnection = $this->container->get('rackspacecloudfiles_connection');
            $this->authenticated = true;
        }

        return $this->rackspaceConnection;
    }

}
