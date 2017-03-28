<?php

namespace Tests\Vich\UploaderBundle\Tests\Form\Type;

use Vich\TestBundle\Entity\Product;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Vich\UploaderBundle\Tests\Form\Type\VichFileTypeTest;

class VichImageTypeTest extends VichFileTypeTest
{
    const TESTED_TYPE = VichImageType::class;

    public function buildViewDataProvider()
    {
        $object = new Product();

        return [
            [
                $object,
                ['download_link' => true, 'download_uri' => null],
                [
                    'object' => $object,
                    'show_download_link' => true,
                    'download_uri' => 'resolved-uri',
                    'value' => null,
                    'attr' => [],
                ],
            ],
            [
                $object,
                ['download_link' => false, 'download_uri' => null],
                [
                    'object' => $object,
                    'show_download_link' => false,
                    'download_uri' => 'resolved-uri',
                    'value' => null,
                    'attr' => [],
                ],
            ],
            [
                $object,
                ['download_link' => true, 'download_uri' => 'custom-uri'],
                [
                    'object' => $object,
                    'show_download_link' => true,
                    'download_uri' => 'custom-uri',
                    'value' => null,
                    'attr' => [],
                ],
            ],
        ];
    }
}
