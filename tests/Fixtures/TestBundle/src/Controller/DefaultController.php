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

class DefaultController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function uploadAction(string $formType): Response
    {
        $form = $this->getForm($this->getImage());

        return $this->render('default/upload.html.twig', [
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

            return $this->redirect($this->generateUrl('view', [
                'formType' => $formType,
                'imageId' => $image->getId(),
            ]));
        }

        return $this->render('default/upload.html.twig', [
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

    private function getImage(?int $imageId = null): Image
    {
        if (null === $imageId) {
            return new Image();
        }

        return $this->doctrine->getRepository(Image::class)->find($imageId);
    }
}
