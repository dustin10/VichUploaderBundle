<?php

namespace Vich\UploaderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Vich\UploaderBundle\Form\DataTransformer\FileTransformer;
use Vich\UploaderBundle\Storage\StorageInterface;

class VichFileType extends AbstractType
{
    private $storage;

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('mapping'));
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
        ));

        $builder->addModelTransformer(new FileTransformer());

        if ($options['allow_delete']) {
            $this->buildDeleteField($builder, $options);
        }
    }

    protected function buildDeleteField(FormBuilderInterface $builder, array $options)
    {
        $builder->add('delete', 'checkbox', array(
            'label'     => 'Supprimer ?', // @todo i18n
            'required'  => false,
            'mapped'    => false,
        ));

        $builder->get('delete')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            $delete = $event->getForm()->getData();
            $entity = $event->getForm()->getParent()->getParent()->getData();

            if (!$delete) {
                return;
            }

            exit('delete');
        });
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['object'] = $form->getParent()->getData();
        //$view->vars['file_property'] = $options['file_property'];

        if ($options['download_link']) {
            try {
                $view->vars['download_uri'] = $this->storage->resolveUri($form->getParent()->getData(), $options['mapping']);
            } catch (\InvalidArgumentException $e) {
                // no file
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'form';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'vich_file';
    }
}
