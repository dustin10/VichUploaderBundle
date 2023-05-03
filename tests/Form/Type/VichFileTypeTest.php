<?php

namespace Vich\UploaderBundle\Tests\Form\Type;

use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPath;
use Vich\TestBundle\Entity\Product;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Tests\TestCase;

final class VichFileTypeTest extends TestCase
{
    protected const TESTED_TYPE = VichFileType::class;

    public function testEmptyDownloadLinkDoNotThrowsDeprecation(): void
    {
        $optionsResolver = new OptionsResolver();

        $storage = $this->createMock(StorageInterface::class);
        $uploadHandler = $this->getUploadHandlerMock();
        $propertyMappingFactory = $this->getPropertyMappingFactoryMock();
        $propertyAccessor = $this->createMock(PropertyAccessor::class);

        $testedType = static::TESTED_TYPE;

        $type = new $testedType($storage, $uploadHandler, $propertyMappingFactory, $propertyAccessor);

        $type->configureOptions($optionsResolver);

        $resolved = $optionsResolver->resolve([]);

        foreach (['download_uri' => true] as $key => $value) {
            self::assertArrayHasKey($key, $resolved);
            self::assertSame($value, $resolved[$key]);
        }
    }

    /**
     * @dataProvider buildViewDataProvider
     */
    public function testBuildView(?Product $object, array $options, array $vars): void
    {
        $field = 'image';

        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->method('resolveUri')
            ->with($object, $field)
            ->willReturn('resolved-uri');

        $parentForm = $this->createMock(FormInterface::class);
        $parentForm
            ->method('getData')
            ->willReturn($object);

        $config = $this->createMock(FormConfigInterface::class);
        $config
            ->method('getOption')
            ->willReturnCallback(fn (string $key) => $options[$key] ?? null);

        $form = $this->createMock(FormInterface::class);
        $form
            ->method('getParent')
            ->willReturn($parentForm);
        $form
            ->method('getName')
            ->willReturn($field);
        $form
            ->method('getConfig')
            ->willReturn($config);

        $uploadHandler = $this->getUploadHandlerMock();
        $propertyMappingFactory = $this->getPropertyMappingFactoryMock();

        $propertyAccessor = $this->createMock(PropertyAccessor::class);

        if (isset($options['download_label'])) {
            if (true === $options['download_label']) {
                $mapping = $this->getPropertyMappingMock();
                $mapping
                    ->expects(self::once())
                    ->method('readProperty')
                    ->with($object, 'originalName')
                    ->willReturn($object->getImageOriginalName());

                $propertyMappingFactory
                    ->expects(self::once())
                    ->method('fromField')
                    ->with($object, $field)
                    ->willReturn($mapping);
            }

            if ($options['download_label'] instanceof PropertyPath) {
                $propertyAccessor
                    ->expects(self::once())
                    ->method('getValue')
                    ->with($object, $options['download_label'])
                    ->willReturn($object->getTitle());
            }
        }

        $testedType = self::TESTED_TYPE;

        $view = new FormView();
        $type = new $testedType($storage, $uploadHandler, $propertyMappingFactory, $propertyAccessor);
        $type->buildView($view, $form, $options);
        self::assertEquals($vars, $view->vars);
    }

    public static function buildViewDataProvider(): array
    {
        $object = new Product();
        $object->setImageOriginalName('image.jpeg');
        $object->setTitle('Product1');

        return [
            [
                $object,
                [
                    'download_label' => 'custom label',
                    'download_uri' => true,
                    'asset_helper' => true,
                ],
                [
                    'object' => $object,
                    'download_label' => 'custom label',
                    'download_uri' => 'resolved-uri',
                    'value' => null,
                    'attr' => [],
                    'asset_helper' => true,
                ],
            ],
            [
                null,
                [
                    'download_label' => 'download',
                    'download_uri' => false,
                    'asset_helper' => false,
                ],
                [
                    'object' => null,
                    'download_uri' => null,
                    'value' => null,
                    'attr' => [],
                    'asset_helper' => false,
                ],
            ],
            [
                $object,
                [
                    'download_label' => 'download',
                    'download_uri' => false,
                    'asset_helper' => false,
                ],
                [
                    'object' => $object,
                    'download_uri' => null,
                    'value' => null,
                    'attr' => [],
                    'asset_helper' => false,
                ],
            ],
            [
                $object,
                [
                    'download_label' => 'download',
                    'download_uri' => static fn (Product $product) => '/download/'.$product->getImageOriginalName(),
                    'asset_helper' => false,
                ],
                [
                    'object' => $object,
                    'download_label' => 'download',
                    'download_uri' => '/download/image.jpeg',
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
                    'asset_helper' => false,
                ],
                [
                    'object' => $object,
                    'download_label' => 'download',
                    'download_uri' => 'custom-uri',
                    'value' => null,
                    'attr' => [],
                    'asset_helper' => false,
                ],
            ],
            [
                $object,
                [
                    'download_label' => true,
                    'download_uri' => true,
                    'asset_helper' => false,
                ],
                [
                    'object' => $object,
                    'download_label' => 'image.jpeg',
                    'translation_domain' => false,
                    'download_uri' => 'resolved-uri',
                    'value' => null,
                    'attr' => [],
                    'asset_helper' => false,
                ],
            ],
            [
                $object,
                [
                    'download_label' => static fn (Product $product) => 'prefix-'.$product->getImageOriginalName(),
                    'download_uri' => true,
                    'asset_helper' => false,
                ],
                [
                    'object' => $object,
                    'download_label' => 'prefix-image.jpeg',
                    'translation_domain' => false,
                    'download_uri' => 'resolved-uri',
                    'value' => null,
                    'attr' => [],
                    'asset_helper' => false,
                ],
            ],
            [
                $object,
                [
                    'download_label' => static fn (Product $product) => [
                        'download_label' => 'prefix-'.$product->getImageOriginalName(),
                        'translation_domain' => 'messages',
                    ],
                    'download_uri' => true,
                    'asset_helper' => false,
                ],
                [
                    'object' => $object,
                    'download_label' => 'prefix-image.jpeg',
                    'translation_domain' => 'messages',
                    'download_uri' => 'resolved-uri',
                    'value' => null,
                    'attr' => [],
                    'asset_helper' => false,
                ],
            ],
            [
                $object,
                [
                    'download_label' => new PropertyPath('title'),
                    'download_uri' => true,
                    'asset_helper' => false,
                ],
                [
                    'object' => $object,
                    'download_label' => $object->getTitle(),
                    'translation_domain' => false,
                    'download_uri' => 'resolved-uri',
                    'value' => null,
                    'attr' => [],
                    'asset_helper' => false,
                ],
            ],
        ];
    }
}
