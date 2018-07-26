<?php

namespace Vich\UploaderBundle\Naming;

interface CallableNameProviderInterface
{
    public function getUploadedFileName(): string;
}
