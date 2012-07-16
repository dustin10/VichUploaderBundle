<?php

namespace Vich\UploaderBundle\Storage\Adapter;

/**
 * CDNAdapterInterface
 *
 ** @author Francesco Tassi <tassi.francesco@gmail.com>
 */
interface CDNAdapterInterface
{

    public function put($filePath, $fileName);

    public function getAbsoluteUri($filename);

    public function remove($filename);
}
