<?php

namespace Vich\UploaderBundle\Naming;

interface CallableDirectoryNameProviderInterface
{
    public function getUploadedDirectoryName(): string;
}
