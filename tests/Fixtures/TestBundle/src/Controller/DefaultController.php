<?php

namespace Vich\TestBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vich\TestBundle\Entity\Image;
use Vich\UploaderBundle\Form\Type as VichType;

final class DefaultController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $doctrine)
    {
    }

    public function uploadAction(string $formType): Response
    {
        $form = $this->getForm($this->getImage());

        return $this->render('default/upload.html.twig', [
            'formType' => $formType,
            'form' => $form->createView(),
        ]);
    }

    public function uploadWithPropertyPathAction(string $formType): Response
    {
        $form = $this->getFormWithPropertyPath($this->getImage());

        return $this->render('default/upload_with_property_path.html.twig', [
            'formType' => $formType,
            'form' => $form->createView(),
        ]);
    }

    public function editAction(string $formType, ?int $imageId): Response
    {
        $form = $this->getForm($this->getImage($imageId));

        return $this->render('default/edit.html.twig', [
            'imageId' => $imageId,
            'formType' => $formType,
            'form' => $form->createView(),
            'image' => $this->getImage($imageId),
        ]);
    }

    public function editWithPropertyPathAction(string $formType, ?int $imageId): Response
    {
        $form = $this->getFormWithPropertyPath($this->getImage($imageId));

        return $this->render('default/edit_with_property_path.html.twig', [
            'imageId' => $imageId,
            'formType' => $formType,
            'form' => $form->createView(),
        ]);
    }

    public function submitAction(Request $request, string $formType, ?int $imageId = null): Response
    {
        $image = $this->getImage($imageId);
        $form = $this->getForm($image)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->doctrine->getManager();

            $em->persist($image);
            $em->flush();

            return $this->redirectToRoute('view', [
                'formType' => $formType,
                'imageId' => $image->getId(),
            ]);
        }

        return $this->render('default/upload.html.twig', [
            'formType' => $formType,
            'form' => $form->createView(),
        ]);
    }

    public function submitWithPropertyPathAction(Request $request, string $formType, ?int $imageId = null): Response
    {
        $image = $this->getImage($imageId);
        $form = $this->getFormWithPropertyPath($image)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->doctrine->getManager();

            $em->persist($image);
            $em->flush();

            return $this->redirectToRoute('view_with_property_path', [
                'formType' => $formType,
                'imageId' => $image->getId(),
            ]);
        }

        return $this->render('default/upload_with_property_path.html.twig', [
            'formType' => $formType,
            'form' => $form->createView(),
        ]);
    }

    private function getForm(Image $image): FormInterface
    {
        return $this->createFormBuilder($image)
            ->add('title', Type\TextType::class)
            ->add('imageFile', VichType\VichImageType::class)
            ->add('save', Type\SubmitType::class)
            ->getForm()
        ;
    }

    private function getFormWithPropertyPath(Image $image): FormInterface
    {
        return $this->createFormBuilder($image)
            ->add('title', Type\TextType::class)
            ->add('image_file', VichType\VichImageType::class, ['property_path' => 'imageFile'])
            ->add('save', Type\SubmitType::class)
            ->getForm()
        ;
    }

    private function getImage(?int $imageId = null): Image
    {
        if (null === $imageId) {
            return new Image();
        }

        return $this->doctrine->getRepository(Image::class)->find($imageId);
    }
}
