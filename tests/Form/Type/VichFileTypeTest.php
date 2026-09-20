<?php

namespace Vich\UploaderBundle\Tests\Form\Type;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validation;
use Vich\TestBundle\Entity\Product;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Handler\UploadHandlerInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingFactoryInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingInterface;
use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Tests\TestCaseTrait;

#[AllowMockObjectsWithoutExpectations]
final class VichFileTypeTest extends TypeTestCase
{
    use TestCaseTrait;

    protected const TESTED_TYPE = VichFileType::class;

    protected StorageInterface&MockObject $storage;
    protected FormInterface&Stub $parentForm;
    protected FormConfigInterface&Stub $config;
    protected FormInterface&Stub $form;
    protected UploadHandlerInterface&Stub $uploadHandler;
    protected PropertyMappingFactoryInterface&MockObject $propertyMappingFactory;
    protected PropertyAccessorInterface&MockObject $propertyAccessor;
    protected PropertyMappingInterface&MockObject $mapping;

    protected function setUp(): void
    {
        $this->storage = $this->createMock(StorageInterface::class);
        $this->parentForm = $this->createStub(FormInterface::class);
        $this->config = $this->createStub(FormConfigInterface::class);

        $this->form = $this->createStub(FormInterface::class);
        $this->form
            ->method('getParent')
            ->willReturn($this->parentForm);
        $this->form
            ->method('getConfig')
            ->willReturn($this->config);

        $this->uploadHandler = $this->getUploadHandlerStub();
        $this->propertyMappingFactory = $this->getPropertyMappingFactoryMock();
        $this->propertyAccessor = $this->createMock(PropertyAccessor::class);
        $this->mapping = $this->getPropertyMappingMock();

        parent::setUp();
    }

    #[Test]
    public function emptyDownloadLinkDoNotThrowsDeprecation(): void
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
    #[Test]
    public function buildView(?Product $object, array $options, array $vars): void
    {
        $field = 'image';

        $this->storage
            ->method('resolveUri')
            ->willReturnMap([[$object, $field, null, 'resolved-uri']]);

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
            new HttpFoundationExtension(),
        ];
    }

    #[Test]
    public function withDeleteField(): void
    {
        $field = 'image';

        $object = new Product();
        $object->setImageOriginalName('image.jpeg');
        $object->setTitle('Product1');

        $this->storage
            ->expects($this->atLeastOnce())
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

    #[DataProvider('uploadErrorProvider')]
    #[Test]
    public function uploadErrorBubblesToTheVichField(int $errorCode, string $expectedMessage): void
    {
        $field = 'image';

        $form = $this->factory->createBuilder(FormType::class, new Product())
            ->add($field, self::TESTED_TYPE, ['allow_delete' => false])
            ->getForm();

        $form->submit([$field => ['file' => new UploadedFile(__FILE__, 'test.php', null, $errorCode, true)]]);

        // the error is added by FileType on the inner "file" child, which no theme renders
        self::assertCount(0, $form[$field]['file']->getErrors());

        $errors = $form[$field]->getErrors();
        self::assertCount(1, $errors);
        self::assertSame($expectedMessage, $errors[0]->getMessageTemplate());
    }

    public static function uploadErrorProvider(): array
    {
        return [
            [\UPLOAD_ERR_INI_SIZE, 'The file is too large. Allowed maximum size is {{ limit }} {{ suffix }}.'],
            [\UPLOAD_ERR_PARTIAL, 'The file could not be uploaded.'],
        ];
    }

    /**
     * An oversized upload also triggers a File constraint violation. ViolationMapper drops the
     * bubbled FileUploadError in that case, so only the constraint message must remain.
     */
    #[Test]
    public function uploadErrorIsNotDuplicatedByTheFileConstraint(): void
    {
        $field = 'image';

        $factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->getFormFactory();

        $form = $factory->createBuilder(FormType::class, new Product())
            ->add($field, self::TESTED_TYPE, [
                'allow_delete' => false,
                'constraints' => [new File(maxSize: '1k')],
            ])
            ->getForm();

        $form->submit([$field => ['file' => new UploadedFile(__FILE__, 'test.php', null, \UPLOAD_ERR_INI_SIZE, true)]]);

        $errors = $form->getErrors(true);
        self::assertCount(1, $errors);
        self::assertSame('The file is too large. Allowed maximum size is {{ limit }} {{ suffix }}.', $errors[0]->getMessageTemplate());
        self::assertSame($field, $errors[0]->getOrigin()->getName());
    }
}
