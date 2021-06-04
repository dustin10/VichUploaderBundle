<?php

namespace Vich\UploaderBundle\Metadata\Driver;

final class YmlDriver extends AbstractYamlDriver
{
    protected function getExtension(): string
    {
        return 'yml';
    }
}
