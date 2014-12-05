<?php

namespace Vich\UploaderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Vich\UploaderBundle\Form\DataTransformer\FileTransformer;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Storage\StorageInterface;

class VichFileType extends AbstractType
{
    protected $storage;
    protected $handler;
    protected $translator;

    public function __construct(StorageInterface $storage, UploadHandler $handler, TranslatorInterface $translator)
    {
        $this->storage = $storage;
        $this->handler = $handler;
        $this->translator = $translator;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'allow_delete'  => true,
            'download_link' => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', 'file', array(
            'required' => $options['required'],
            'label'    => $options['label'],
            'attr'     => $options['attr'],
        ));

        $builder->addModelTransformer(new FileTransformer());

        if ($options['allow_delete']) {
            $this->buildDeleteField($builder, $options);
        }
    }

    protected function buildDeleteField(FormBuilderInterface $builder, array $options)
    {
        // add delete only if there is a file
        $storage = $this->storage;
        $translator = $this->translator;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options, $storage, $translator) {
            $form = $event->getForm();
            $object = $form->getParent()->getData();

            // no object or no uploaded file: no delete button
            if (null === $object || null === $storage->resolvePath($object, $form->getName())) {
                return;
            }

            $form->add('delete', 'checkbox', array(
                'label'     => $translator->trans('form.label.delete', array(), 'VichUploaderBundle'),
                'required'  => false,
                'mapped'    => false,
            ));
        });

        // delete file if needed
        $handler = $this->handler;
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options, $handler) {
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
    public function getName()
    {
        return 'vich_file';
    }
}
