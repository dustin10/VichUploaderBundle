<?php

namespace Vich\UploaderBundle\Tests\Form\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPath;
use Vich\TestBundle\Entity\Product;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Handler\UploadHandlerInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Tests\TestCaseTrait;

final class VichFileTypeTest extends TypeTestCase
{
    use TestCaseTrait;

    protected const TESTED_TYPE = VichFileType::class;

    protected StorageInterface|MockObject $storage;
    protected FormInterface|MockObject $parentForm;
    protected FormConfigInterface|MockObject $config;
    protected FormInterface|MockObject $form;
    protected UploadHandlerInterface|MockObject $uploadHandler;
    protected PropertyMappingFactory|MockObject $propertyMappingFactory;
    protected PropertyAccessorInterface|MockObject $propertyAccessor;
    protected PropertyMapping|MockObject $mapping;

    protected function setUp(): void
    {
        $this->storage = $this->createMock(StorageInterface::class);
        $this->parentForm = $this->createMock(FormInterface::class);
        $this->config = $this->createMock(FormConfigInterface::class);

        $this->form = $this->createMock(FormInterface::class);
        $this->form
            ->method('getParent')
            ->willReturn($this->parentForm);
        $this->form
            ->method('getConfig')
            ->willReturn($this->config);

        $this->uploadHandler = $this->getUploadHandlerMock();
        $this->storage = $this->createMock(StorageInterface::class);
        $this->uploadHandler = $this->getUploadHandlerMock();
        $this->propertyMappingFactory = $this->getPropertyMappingFactoryMock();
        $this->propertyAccessor = $this->createMock(PropertyAccessor::class);
        $this->mapping = $this->getPropertyMappingMock();

        parent::setUp();
    }

    public function testEmptyDownloadLinkDoNotThrowsDeprecation(): void
    {
        $optionsResolver = new OptionsResolver();

        $testedType = self::TESTED_TYPE;
        $type = new $testedType($this->storage, $this->uploadHandler, $this->propertyMappingFactory, $this->propertyAccessor);
        $type->configureOptions($optionsResolver);

        $resolved = $optionsResolver->resolve([]);

        foreach (['download_uri' => true] as $key => $value) {
            self::assertArrayHasKey($key, $resolved);
            self::assertSame($value, $resolved[$key]);
        }
    }

    #[DataProvider('buildViewDataProvider')]
    public function testBuildView(?Product $object, array $options, array $vars): void
    {
        $field = 'image';

        $this->storage
            ->method('resolveUri')
            ->with($object, $field)
            ->willReturn('resolved-uri');

        $this->parentForm
            ->method('getData')
            ->willReturn($object);

        $this->config
            ->method('getOption')
            ->willReturnCallback(static fn (string $key) => $options[$key] ?? null);

        $this->form
            ->method('getParent')
            ->willReturn($this->parentForm);
        $this->form
            ->method('getName')
            ->willReturn($field);
        $this->form
            ->method('getConfig')
            ->willReturn($this->config);

        if (isset($options['download_label'])) {
            if (true === $options['download_label']) {
                $this->mapping
                    ->expects(self::once())
                    ->method('readProperty')
                    ->with($object, 'originalName')
                    ->willReturn($object->getImageOriginalName());

                $this->propertyMappingFactory
                    ->expects(self::once())
                    ->method('fromField')
                    ->with($object, $field)
                    ->willReturn($this->mapping);
            }

            if ($options['download_label'] instanceof PropertyPath) {
                $this->propertyAccessor
                    ->expects(self::once())
                    ->method('getValue')
                    ->with($object, $options['download_label'])
                    ->willReturn($object->getTitle());
            }
        }

        $testedType = self::TESTED_TYPE;

        $view = new FormView();
        $type = new $testedType($this->storage, $this->uploadHandler, $this->propertyMappingFactory, $this->propertyAccessor);
        $type->buildView($view, $this->form, $options);
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
                    'download_label_translation_domain' => null,
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
                    'download_label_translation_domain' => null,
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
                    'download_label_translation_domain' => null,
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
                    'download_label_translation_domain' => false,
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
                    'download_label_translation_domain' => false,
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
                    'download_label_translation_domain' => 'messages',
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
                    'download_label_translation_domain' => false,
                    'download_uri' => 'resolved-uri',
                    'value' => null,
                    'attr' => [],
                    'asset_helper' => false,
                ],
            ],
            [
                $object,
                [
                    'download_label' => 'custom label',
                    'download_label_translation_domain' => 'custom_domain',
                    'download_uri' => true,
                    'asset_helper' => true,
                ],
                [
                    'object' => $object,
                    'download_label' => 'custom label',
                    'download_label_translation_domain' => 'custom_domain',
                    'download_uri' => 'resolved-uri',
                    'value' => null,
                    'attr' => [],
                    'asset_helper' => true,
                ],
            ],
        ];
    }

    protected function getExtensions(): array
    {
        // create a type instance with the mocked dependencies
        $type = new (self::TESTED_TYPE)($this->storage, $this->uploadHandler, $this->propertyMappingFactory, $this->propertyAccessor);

        return [
            // register the type instances with the PreloadedExtension
            new PreloadedExtension([$type], []),
        ];
    }

    public function testWithDeleteField(): void
    {
        $field = 'image';

        $object = new Product();
        $object->setImageOriginalName('image.jpeg');
        $object->setTitle('Product1');

        // $storage = $this->createMock(StorageInterface::class);

        $this->storage
            ->method('resolveUri')
            ->with($object, $field)
            ->willReturn('resolved-uri');

        $options = [
            'allow_delete' => true,
            'delete_label' => 'custom delete label',
            'delete_label_translation_domain' => 'custom domain',
        ];

        $expectedDeleteViewVars = [
            'label' => 'custom delete label',
            'translation_domain' => 'custom domain',
        ];

        // -- Really needs to build the form
        $formBuilder = $this->factory->createBuilder(FormType::class, $object)
            ->add($field, self::TESTED_TYPE, $options);
        $form = $formBuilder->getForm();
        self::assertTrue($form[$field]->has('delete'));

        $deleteFieldView = $form[$field]['delete']->createView();

        foreach ($expectedDeleteViewVars as $key => $var) {
            self::assertArrayHasKey($key, $deleteFieldView->vars);
            self::assertEquals($var, $deleteFieldView->vars[$key]);
        }
    }
}
