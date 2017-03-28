<?php

namespace Vich\UploaderBundle\Form\Type;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class VichImageType extends VichFileType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $object = $form->getParent()->getData();

        $view->vars['object'] = $object;
        $view->vars['show_download_link'] = $options['download_link'];

        if ($object) {
            $view->vars['download_uri'] = $options['download_uri']
                ?: $this->storage->resolveUri($object, $form->getName());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'vich_image';
    }
}
