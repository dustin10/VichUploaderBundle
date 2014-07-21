<?php

namespace Vich\UploaderBundle\Tests;

class TestCase extends \PHPUnit_Framework_TestCase
{
    protected function getUploadedFileMock()
    {
        return $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->setConstructorArgs(array('lala', 'lala', $mimeType = null, $size = null, $error = 'other than UPLOAD_ERR_OK', $test = true))
            ->getMock();
    }
}
