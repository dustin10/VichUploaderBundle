<?php

namespace Vich\TestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\TestBundle\Entity\ValidatedImage;
use Vich\UploaderBundle\Form\Type\VichFileType;

class ValidatedImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'Enter title...'],
            ])
            ->add('imageFile', VichFileType::class, [
                'required' => false, // Let the validator handle this
                'allow_delete' => true,
                'download_uri' => true,
                'download_label' => 'Download current file',
                'delete_label' => 'Delete file',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ValidatedImage::class,
        ]);
    }
}
