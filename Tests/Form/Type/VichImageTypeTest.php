<?php

namespace Tests\Vich\UploaderBundle\Tests\Form\Type;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Vich\TestBundle\Entity\Product;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Tests\Form\Type\VichFileTypeTest;

class VichImageTypeTest extends VichFileTypeTest
{
    const TESTED_TYPE = VichImageType::class;

    public function buildViewDataProvider(): array
    {
        $object = new Product();

        return [
            [
                $object,
                [
                    'download_uri' => true,
                    'download_label' => 'download',
                    'image_uri' => false,
                    'imagine_pattern' => null,
                    'asset_helper' => false,
                ],
                [
                    'object' => $object,
                    'download_uri' => 'resolved-uri',
                    'download_label' => 'download',
                    'image_uri' => null,
                    'show_download_link' => true,
                    'value' => null,
                    'attr' => [],
                    'asset_helper' => false,
                ],
            ],
            [
                null,
                [
                    'download_uri' => true,
                    'download_label' => 'download',
                    'image_uri' => false,
                    'imagine_pattern' => null,
                    'asset_helper' => false,
                ],
                [
                    'object' => null,
                    'download_uri' => null,
                    'image_uri' => null,
                    'show_download_link' => false,
                    'value' => null,
                    'attr' => [],
                    'asset_helper' => false,
                ],
            ],
            [
                $object,
                [
                    'download_uri' => false,
                    'download_label' => 'download',
                    'image_uri' => true,
                    'imagine_pattern' => null,
                    'asset_helper' => false,
                ],
                [
                    'object' => $object,
                    'download_uri' => false,
                    'download_label' => 'download',
                    'image_uri' => 'resolved-uri',
                    'show_download_link' => false,
                    'value' => null,
                    'attr' => [],
                    'asset_helper' => false,
                ],
            ],
            [
                $object,
                [
                    'download_label' => 'download',
                    'download_uri' => 'custom-uri',
                    'image_uri' => true,
                    'imagine_pattern' => null,
                    'asset_helper' => false,
                ],
                [
                    'object' => $object,
                    'download_uri' => 'custom-uri',
                    'download_label' => 'download',
                    'show_download_link' => true,
                    'image_uri' => 'resolved-uri',
                    'value' => null,
                    'attr' => [],
                    'asset_helper' => false,
                ],
            ],
            [
                $object,
                [
                    'download_label' => 'download',
                    'download_uri' => 'custom-uri',
                    'image_uri' => 'image_uri',
                    'imagine_pattern' => null,
                    'asset_helper' => false,
                ],
                [
                    'object' => $object,
                    'download_uri' => 'custom-uri',
                    'download_label' => 'download',
                    'show_download_link' => true,
                    'image_uri' => 'image_uri',
                    'value' => null,
                    'attr' => [],
                    'asset_helper' => false,
                ],
            ],
            [
                $object,
                [
                    'download_label' => 'download',
                    'download_uri' => 'custom-uri',
                    'image_uri' => static function (Product $product, $resolvedUri) {
                        return 'prefix-'.$resolvedUri;
                    },
                    'imagine_pattern' => null,
                    'asset_helper' => false,
                ],
                [
                    'object' => $object,
                    'download_uri' => 'custom-uri',
                    'download_label' => 'download',
                    'show_download_link' => true,
                    'image_uri' => 'prefix-resolved-uri',
                    'value' => null,
                    'attr' => [],
                    'asset_helper' => false,
                ],
            ],
        ];
    }

    public function testLiipImagineBundleIntegration(): void
    {
        if (!\class_exists(CacheManager::class)) {
            $this->markTestSkipped('LiipImagineBundle is not installed.');
        }

        $field = 'image';
        $object = new Product();

        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->expects($this->any())
            ->method('resolveUri')
            ->with($object, $field)
            ->willReturn('resolved-uri');

        $parentForm = $this->createMock(FormInterface::class);
        $parentForm
            ->expects($this->any())
            ->method('getData')
            ->willReturn($object);

        $form = $this->createMock(FormInterface::class);
        $form
            ->expects($this->any())
            ->method('getParent')
            ->willReturn($parentForm);
        $form
            ->expects($this->any())
            ->method('getName')
            ->willReturn($field);

        $uploadHandler = $this->createMock(UploadHandler::class);
        $propertyMappingFactory = $this->createMock(PropertyMappingFactory::class);

        $propertyAccessor = $this->createMock(PropertyAccessor::class);
        $cacheManager = $this->createMock(CacheManager::class);

        $cacheManager
            ->expects($this->once())
            ->method('getBrowserPath')
            ->with('resolved-uri', 'product_sq200')
            ->willReturn('product_sq200/resolved-uri');

        $testedType = static::TESTED_TYPE;

        $view = new FormView();
        $type = new $testedType($storage, $uploadHandler, $propertyMappingFactory, $propertyAccessor, $cacheManager);

        $options = [
            'download_label' => 'download',
            'download_uri' => 'custom-uri',
            'image_uri' => true,
            'imagine_pattern' => 'product_sq200',
            'asset_helper' => false,
        ];

        $vars = [
            'object' => $object,
            'download_uri' => 'custom-uri',
            'download_label' => 'download',
            'show_download_link' => true,
            'image_uri' => 'product_sq200/resolved-uri',
            'value' => null,
            'attr' => [],
            'asset_helper' => false,
        ];

        $type->buildView($view, $form, $options);
        $this->assertEquals($vars, $view->vars);
    }

    public function testLiipImagineBundleIntegrationThrownExceptionIfNotAvailable(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('LiipImagineBundle must be installed and configured for using "imagine_pattern" option.');

        $object = new Product();

        $testedType = static::TESTED_TYPE;

        $storage = $this->createMock(StorageInterface::class);
        $uploadHandler = $this->createMock(UploadHandler::class);
        $propertyMappingFactory = $this->createMock(PropertyMappingFactory::class);
        $propertyAccessor = $this->createMock(PropertyAccessor::class);

        $parentForm = $this->createMock(FormInterface::class);
        $parentForm
            ->expects($this->any())
            ->method('getData')
            ->willReturn($object);

        $form = $this->createMock(FormInterface::class);
        $form
            ->expects($this->any())
            ->method('getParent')
            ->willReturn($parentForm);

        $view = new FormView();
        $type = new $testedType($storage, $uploadHandler, $propertyMappingFactory, $propertyAccessor);
        $type->buildView($view, $form, ['imagine_pattern' => 'product_sq200']);
    }
}
