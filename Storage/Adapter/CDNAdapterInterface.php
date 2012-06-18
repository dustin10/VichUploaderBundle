<?php

namespace Vich\UploaderBundle\Storage\Adapter;

/**
 * CDNAdapterInterface
 *
 ** @author Francesco Tassi <tassi.francesco@gmail.com>
 */
interface CDNAdapterInterface
{

    function put($filePath, $fileName);
    
    function getAbsoluteUri($filename);
    
    function remove($filename);
}