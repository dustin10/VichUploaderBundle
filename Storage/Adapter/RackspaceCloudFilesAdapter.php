<?php

namespace Vich\UploaderBundle\Storage\Adapter;

use Vich\UploaderBundle\Storage\Adapter\CDNAdapterInterface;

/**
 * Description of RackspaceCloudFilesAdapter
 *
 * @author ftassi
 */
class RackspaceCloudFilesAdapter implements CDNAdapterInterface
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
    protected $container;
    
    function __construct(\CF_Authentication $rackspaceAuthentication, \CF_Connection $rackspaceConnection)
    {
        $this->rackspaceAuthentication = $rackspaceAuthentication;
        $this->rackspaceConnection = $rackspaceConnection;
    }

    /**
     * @return string
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     *
     * @param string $container 
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    
    /**
     *
     * @param string $filename 
     * @return string 
     */
    public function getAbsoluteUri($filename)
    {
        $this->rackspaceAuthentication->authenticate();
        $mediaContainer = $this->rackspaceConnection->get_container($this->container);
        $object = $mediaContainer->get_object($filename);
        return $object->public_uri();
    }

    /**
     *
     * @param string $filePath
     * @param string $fileName 
     */
    public function put($filePath, $fileName)
    {
        $this->rackspaceAuthentication->authenticate();
        $mediaContainer = $this->rackspaceConnection->get_container($this->container);
        $object = $mediaContainer->create_object($fileName);
        return $object->load_from_filename($filePath);        
    }

}

?>
