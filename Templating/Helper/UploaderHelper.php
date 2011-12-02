<?php

namespace Vich\UploaderBundle\Templating\Helper;

use Symfony\Component\Templating\Helper\Helper;
use Vich\UploaderBundle\Upload\UploaderInterface;
use Vich\UploaderBundle\Model\UploadableInterface;

/**
 * UploaderHelper.
 * 
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class UploaderHelper extends Helper
{
    /**
     * @var UploaderInterface $uploader
     */
    private $uploader;
    
    /**
     * Constructs a new instance of GeographicalExtension.
     * 
     * @param UploaderInterface $uploader
     */
    public function __construct(UploaderInterface $uploader)
    {
        $this->uploader = $uploader;
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
     * Gets the public path for the file associated with the uploadable 
     * object.
     * 
     * @param UploadableInterface $uploadable The uploadable object.
     * @return string The public path.
     */
    public function asset(UploadableInterface $uploadable)
    {
        return $this->uploader->getPublicPath($uploadable);
    }
}
