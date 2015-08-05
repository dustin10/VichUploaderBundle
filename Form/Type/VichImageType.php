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
        $view->vars['object'] = $form->getParent()->getData();
        $view->vars['show_download_link'] = $options['download_link'];

        if ($view->vars['object']) {
            $view->vars['download_uri'] = $this->storage->resolveUri($form->getParent()->getData(), $form->getName());
            if (true) { /* @TODO Check whether force download. Could be use config key */
                $view->vars['download_uri'] = $this->router->generate('vich_force_download', [
                    'download_uri' => $view->vars['download_uri'],
                ]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'vich_image';
    }
}
