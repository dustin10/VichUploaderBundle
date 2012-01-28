<?php

namespace Vich\UploaderBundle\Templating\Helper;

use Symfony\Component\Templating\Helper\Helper;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * UploaderHelper.
 * 
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class UploaderHelper extends Helper
{
    /**
     * @var \Vich\UploaderBundle\Storage\StorageInterface $storage
     */
    protected $storage;

    /**
     * @var string $webDirName
     */
    protected $webDirName;
    
    /**
     * Constructs a new instance of UploaderHelper.
     *
     * @param \Vich\UploaderBundle\Storage\StorageInterface $storage The storage.
     * @param string $webDirName The name of the application's web directory.
     */
    public function __construct(StorageInterface $storage, $webDirName)
    {
        $this->storage = $storage;
        $this->webDirName = $webDirName;
    }
    
    /**
     * Gets the helper name.
     * 
     * @return string The name
     */
    public function getName()
    {
        return 'vich_uploader';
    }
    
    /**
     * Gets the public path for the file associated with the
     * object.
     * 
     * @param object $obj The object.
     * @param string $field The field.
     * @return string The public asset path.
     */
    public function asset($obj, $field)
    {
        $path = $this->storage->resolvePath($obj, $field);

        $index = strpos($path, $this->webDirName);

        return substr($path, $index + strlen($this->webDirName));
    }
}
