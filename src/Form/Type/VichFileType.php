<?php

namespace Vich\UploaderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPath;
use Vich\UploaderBundle\Form\DataTransformer\FileTransformer;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 * @author Massimiliano Arione <max.arione@gmail.com>
 */
class VichFileType extends AbstractType
{
    protected readonly PropertyAccessorInterface $propertyAccessor;

    public function __construct(
        protected readonly StorageInterface $storage,
        protected readonly UploadHandler $handler,
        protected readonly PropertyMappingFactory $factory,
        PropertyAccessorInterface $propertyAccessor = null
    ) {
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_delete' => true,
            'asset_helper' => false,
            'download_uri' => true,
            'download_label' => 'vich_uploader.link.download',
            'delete_label' => 'vich_uploader.form_label.delete_confirm',
            'error_bubbling' => false,
        ]);

        $resolver->setAllowedTypes('allow_delete', 'bool');
        $resolver->setAllowedTypes('asset_helper', 'bool');
        $resolver->setAllowedTypes('download_uri', ['bool', 'string', 'callable']);
        $resolver->setAllowedTypes('download_label', ['bool', 'string', 'callable', PropertyPath::class]);
        $resolver->setAllowedTypes('error_bubbling', 'bool');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('file', Type\FileType::class, [
            'required' => $options['required'],
            'label' => $options['label'],
            'attr' => $options['attr'],
            'translation_domain' => $options['translation_domain'],
        ]);

        $builder->addModelTransformer(new FileTransformer());

        if ($options['allow_delete']) {
            $this->buildDeleteField($builder, $options);
        }
    }

    protected function buildDeleteField(FormBuilderInterface $builder, array $options): void
    {
        // add delete only if there is a file
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
            $form = $event->getForm();
            $parent = $form->getParent();
            // no object: no delete button
            if (null === $parent) {
                return;
            }
            $object = $parent->getData();

            // no object or no uploaded file: no delete button
            if (null === $object || null === $this->storage->resolveUri($object, $this->getFieldName($form))) {
                return;
            }

            $form->add('delete', Type\CheckboxType::class, [
                'label' => $options['delete_label'],
                'mapped' => false,
                'translation_domain' => $options['translation_domain'],
                'required' => false,
            ]);
        });

        // delete file if needed
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            $form = $event->getForm();
            $object = $form->getParent()->getData();
            $delete = $form->has('delete') ? $form->get('delete')->getData() : false;

            if (!$delete) {
                return;
            }

            $this->handler->remove($object, $this->getFieldName($form));
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $object = $form->getParent()->getData();
        $view->vars['object'] = $object;

        $view->vars['download_uri'] = null;
        if ($options['download_uri'] && $object) {
            $view->vars['download_uri'] = $this->resolveUriOption($options['download_uri'], $object, $form);
            $view->vars = \array_replace(
                $view->vars,
                $this->resolveDownloadLabel($options['download_label'], $object, $form)
            );
        }

        $view->vars['asset_helper'] = $options['asset_helper'];
    }

    public function getBlockPrefix(): string
    {
        return 'vich_file';
    }

    final protected function getFieldName(FormInterface $form): string
    {
        return $form->getConfig()->getOption('property_path') ?? $form->getName();
    }

    protected function resolveUriOption(mixed $uriOption, object $object, FormInterface $form): string|bool|null
    {
        if (true === $uriOption) {
            return $this->storage->resolveUri($object, $this->getFieldName($form));
        }

        if (\is_callable($uriOption)) {
            return $uriOption($object, $this->storage->resolveUri($object, $this->getFieldName($form)));
        }

        return $uriOption;
    }

    protected function resolveDownloadLabel(mixed $downloadLabel, object $object, FormInterface $form): array
    {
        if (true === $downloadLabel) {
            $fieldName = $this->getFieldName($form);
            $mapping = $this->factory->fromField($object, $fieldName);
            if (null === $mapping) {
                throw new \UnexpectedValueException(\sprintf('Cannot find mapping for "%s" field', $fieldName));
            }

            return ['download_label' => $mapping->readProperty($object, 'originalName'), 'translation_domain' => false];
        }

        if (\is_callable($downloadLabel)) {
            $result = $downloadLabel($object);

            return [
                'download_label' => $result['download_label'] ?? $result,
                'translation_domain' => $result['translation_domain'] ?? false,
            ];
        }

        if ($downloadLabel instanceof PropertyPath) {
            return [
                'download_label' => $this->propertyAccessor->getValue($object, $downloadLabel),
                'translation_domain' => false,
            ];
        }

        return ['download_label' => $downloadLabel];
    }
}
