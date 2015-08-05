<?php

namespace Vich\UploaderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class VichController
 *
 * @author Victor Bocharsky <bocharsky.bw@gmail.com>
 */
class VichController extends Controller
{
    /**
     * @param string $download_uri
     *
     * @return BinaryFileResponse
     */
    public function forceDownloadAction($download_uri)
    {
        $path = $this->get('kernel')->getRootDir() . '/../web' . $download_uri;

        return new BinaryFileResponse($path);
    }
}