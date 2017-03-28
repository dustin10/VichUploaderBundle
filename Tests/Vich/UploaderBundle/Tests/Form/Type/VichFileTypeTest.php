<?php

namespace Vich\UploaderBundle\Tests\Form\Type;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Vich\TestBundle\Entity\Product;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Storage\StorageInterface;

class VichFileTypeTest extends TestCase
{
    const TESTED_TYPE = VichFileType::class;

    /**
     * @dataProvider buildViewDataProvider
     */
    public function testBuildView($object, array $options, array $vars)
    {
        $field = 'image';

        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->expects($this->any())
            ->method('resolveUri')
            ->with($object, $field)
            ->will($this->returnValue('resolved-uri'));

        $parentForm = $this->createMock(FormInterface::class);
        $parentForm
            ->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($object));

        $form = $this->createMock(FormInterface::class);
        $form
            ->expects($this->any())
            ->method('getParent')
            ->will($this->returnValue($parentForm));
        $form
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($field));

        $testedType = static::TESTED_TYPE;

        $view = new FormView();
        $type = new $testedType($storage, $this->createMock(UploadHandler::class));
        $type->buildView($view, $form, $options);
        $this->assertEquals($vars, $view->vars);
    }

    public function buildViewDataProvider()
    {
        $object = new Product();

        return [
            [
                $object,
                ['download_link' => true, 'download_uri' => null],
                ['object' => $object, 'download_uri' => 'resolved-uri', 'value' => null, 'attr' => []],
            ],
            [
                $object,
                ['download_link' => false, 'download_uri' => null],
                ['object' => $object, 'value' => null, 'attr' => []],
            ],
            [
                $object,
                ['download_link' => true, 'download_uri' => 'custom-uri'],
                ['object' => $object, 'download_uri' => 'custom-uri', 'value' => null, 'attr' => []],
            ],
        ];
    }
}
