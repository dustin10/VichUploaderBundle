<?php

namespace Vich\UploaderBundle\Tests\Form\Type;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Vich\TestBundle\Entity\Product;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Tests\TestCase;

final class VichImageTypeTest extends TestCase
{
    protected const TESTED_TYPE = VichImageType::class;

    public static function buildViewDataProvider(): array
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
                    'image_uri' => static fn (Product $product, $resolvedUri) => 'prefix-'.$resolvedUri,
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

    /**
     * @dataProvider getLiipImagineBundleIntegrationData
     *
     * @requires function CacheManager::_construct
     */
    public function testLiipImagineBundleIntegration(
        string $field,
        Product $object,
        int $storageResolveMethod,
        string $storageResolveMethodName,
        array $storageResolveArguments,
        string $storageResolvedPath,
        string $imaginePattern,
        string $imagineResolvedPath
    ): void {
        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->expects($this->atLeastOnce())
            ->method($storageResolveMethodName)
            ->with(...$storageResolveArguments)
            ->willReturn($storageResolvedPath);

        $parentForm = $this->createMock(FormInterface::class);
        $parentForm
            ->method('getData')
            ->willReturn($object);

        $form = $this->createMock(FormInterface::class);
        $form
            ->method('getParent')
            ->willReturn($parentForm);
        $form
            ->method('getName')
            ->willReturn($field);

        $uploadHandler = $this->getUploadHandlerMock();
        $propertyMappingFactory = $this->getPropertyMappingFactoryMock();

        $propertyAccessor = $this->createMock(PropertyAccessor::class);
        $cacheManager = $this->createMock(CacheManager::class);

        $cacheManager
            ->expects(self::once())
            ->method('getBrowserPath')
            ->with($storageResolvedPath, $imaginePattern)
            ->willReturn($imagineResolvedPath);

        $testedType = self::TESTED_TYPE;

        $view = new FormView();
        $type = new $testedType($storage, $uploadHandler, $propertyMappingFactory, $propertyAccessor, $cacheManager);

        $options = [
            'download_label' => 'download',
            'download_uri' => 'custom-uri',
            'image_uri' => true,
            'imagine_pattern' => $imaginePattern,
            'storage_resolve_method' => $storageResolveMethod,
            'asset_helper' => false,
        ];

        $vars = [
            'object' => $object,
            'download_uri' => 'custom-uri',
            'download_label' => 'download',
            'show_download_link' => true,
            'image_uri' => $imagineResolvedPath,
            'value' => null,
            'attr' => [],
            'asset_helper' => false,
        ];

        $type->buildView($view, $form, $options);
        self::assertEquals($vars, $view->vars);
    }

    public function getLiipImagineBundleIntegrationData(): array
    {
        $field = 'image';
        $object = new Product();

        return [
            'calling StorageInterface::resolveUri()' => [
                $field,
                $object,
                VichImageType::STORAGE_RESOLVE_URI,
                'resolveUri',
                [$object, $field],
                'resolved-uri',
                'product_sq200',
                'product_sq200/resolved-uri',
            ],
            'calling StorageInterface::resolvePath()' => [
                $field,
                $object,
                VichImageType::STORAGE_RESOLVE_PATH_ABSOLUTE,
                'resolvePath',
                [$object, $field],
                'resolved-path-absolute',
                'product_sq200',
                'product_sq200/resolved-path-absolute',
            ],
            'calling StorageInterface::resolvePath() with argument $relative = true' => [
                $field,
                $object,
                VichImageType::STORAGE_RESOLVE_PATH_RELATIVE,
                'resolvePath',
                [$object, $field, null, true],
                'resolved-path-relative',
                'product_sq200',
                'product_sq200/resolved-path-relative',
            ],
        ];
    }

    public function testLiipImagineBundleIntegrationThrownExceptionIfNotAvailable(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('LiipImagineBundle must be installed and configured for using "imagine_pattern" option.');

        $object = new Product();

        $testedType = self::TESTED_TYPE;

        $storage = $this->createMock(StorageInterface::class);
        $uploadHandler = $this->getUploadHandlerMock();
        $propertyMappingFactory = $this->getPropertyMappingFactoryMock();
        $propertyAccessor = $this->createMock(PropertyAccessor::class);

        $parentForm = $this->createMock(FormInterface::class);
        $parentForm
            ->method('getData')
            ->willReturn($object);

        $form = $this->createMock(FormInterface::class);
        $form
            ->method('getParent')
            ->willReturn($parentForm);
        $form
            ->method('getConfig')
            ->willReturn($this->createMock(FormConfigInterface::class));

        $view = new FormView();
        $type = new $testedType($storage, $uploadHandler, $propertyMappingFactory, $propertyAccessor);
        $type->buildView($view, $form, ['imagine_pattern' => 'product_sq200']);
    }
}
