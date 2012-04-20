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
    
    function __construct(\CF_Authentication $rackspaceAuthentication)
    {
        $this->rackspaceAuthentication = $rackspaceAuthentication;
        $this->rackspaceAuthentication->authenticate();
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
     * @param string $filename 
     * @return string 
     */
    public function getAbsoluteUri($filename)
    {
        
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
        $mediaContainer = $this->rackspaceConnection->get_container($this->container);
        $object = $mediaContainer->create_object($fileName);
        return $object->load_from_filename($filePath);        
    }
    
    /**
     *
     * @param string $fileName
     * @return boolean
     */
    public function remove($fileName)
    {
        $mediaContainer = $this->rackspaceConnection->get_container($this->container);
        return $mediaContainer->delete_object($fileName);
    }

}

?>
