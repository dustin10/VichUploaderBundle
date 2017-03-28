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
use Vich\UploaderBundle\Form\DataTransformer\FileTransformer;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Storage\StorageInterface;

class VichFileType extends AbstractType
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var UploadHandler
     */
    protected $handler;

    /**
     * @param StorageInterface $storage
     * @param UploadHandler    $handler
     */
    public function __construct(StorageInterface $storage, UploadHandler $handler)
    {
        $this->storage = $storage;
        $this->handler = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_delete' => true,
            'download_link' => true,
            'error_bubbling' => false,
            'download_uri' => null,
        ]);

        $resolver->setAllowedTypes('allow_delete', 'bool');
        $resolver->setAllowedTypes('download_link', 'bool');
        $resolver->setAllowedTypes('error_bubbling', 'bool');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', Type\FileType::class, [
            'required' => $options['required'],
            'label' => $options['label'],
            'attr' => $options['attr'],
        ]);

        $builder->addModelTransformer(new FileTransformer());

        if ($options['allow_delete']) {
            $this->buildDeleteField($builder, $options);
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    protected function buildDeleteField(FormBuilderInterface $builder, array $options)
    {
        // add delete only if there is a file
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $object = $form->getParent()->getData();

            // no object or no uploaded file: no delete button
            if (null === $object || null === $this->storage->resolveUri($object, $form->getName())) {
                return;
            }

            $form->add('delete', Type\CheckboxType::class, [
                'label' => 'form.label.delete',
                'translation_domain' => 'VichUploaderBundle',
                'required' => false,
                'mapped' => false,
            ]);
        });

        // delete file if needed
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $delete = $form->has('delete') ? $form->get('delete')->getData() : false;
            $object = $form->getParent()->getData();

            if (!$delete) {
                return;
            }

            $this->handler->remove($object, $form->getName());
        });
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $object = $form->getParent()->getData();
        $view->vars['object'] = $object;

        if ($options['download_link'] && $object) {
            $view->vars['download_uri'] = $options['download_uri']
                ?: $this->storage->resolveUri($object, $form->getName());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'vich_file';
    }
}
