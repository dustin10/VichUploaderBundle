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
        ]);
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
        $storage = $this->storage;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options, $storage) {
            $form = $event->getForm();
            $object = $form->getParent()->getData();

            // no object or no uploaded file: no delete button
            if (null === $object || null === $storage->resolveUri($object, $form->getName())) {
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
        $handler = $this->handler;
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($handler) {
            $delete = $event->getForm()->has('delete') ? $event->getForm()->get('delete')->getData() : false;
            $entity = $event->getForm()->getParent()->getData();

            if (!$delete) {
                return;
            }

            $handler->remove($entity, $event->getForm()->getName());
        });
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['object'] = $form->getParent()->getData();

        if ($options['download_link'] && $view->vars['object']) {
            $view->vars['download_uri'] = $this->storage->resolveUri($form->getParent()->getData(), $form->getName());
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
