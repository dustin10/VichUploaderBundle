<?php

namespace Vich\UploaderBundle\Templating\Helper;

use Symfony\Component\Templating\Helper\Helper;
use Vich\UploaderBundle\Upload\UploaderInterface;

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
     * Gets the public path for the file associated with the
     * object.
     * 
     * @param object $obj The object.
     * @param string $field The field.
     * @return string The public path.
     */
    public function asset($obj, $field)
    {
        return $this->uploader->getPublicPath($obj, $field);
    }
}
