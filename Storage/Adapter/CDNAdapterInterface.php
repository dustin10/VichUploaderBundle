<?php

namespace Vich\UploaderBundle\Storage\Adapter;

/**
 * CDNAdapterInterface
 *
 * @author ftassi
 */
interface CDNAdapterInterface
{

    function put($filePath, $fileName);
    
    function getAbsoluteUri($filename);
}

?>