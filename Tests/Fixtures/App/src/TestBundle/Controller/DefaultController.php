<?php

namespace Vich\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpFoundation\Request;
use Vich\TestBundle\Entity\Image;
use Vich\UploaderBundle\Form\Type as VichType;

class DefaultController extends Controller
{
    public function uploadAction($formType)
    {
        $form = $this->getForm($formType, $this->getImage());

        return $this->render('VichTestBundle:Default:upload.html.twig', [
            'formType' => $formType,
            'form' => $form->createView(),
        ]);
    }

    public function editAction($formType, $imageId)
    {
        $form = $this->getForm($formType, $this->getImage($imageId));

        return $this->render('VichTestBundle:Default:edit.html.twig', [
            'imageId' => $imageId,
            'formType' => $formType,
            'form' => $form->createView(),
        ]);
    }

    public function submitAction(Request $request, $formType, $imageId = null)
    {
        $image = $this->getImage($imageId);
        $form = $this->getForm($formType, $image);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($image);
            $em->flush();

            return $this->redirect($this->generateUrl('view', [
                'formType' => $formType,
                'imageId' => $image->getId(),
            ]));
        }

        return $this->render('VichTestBundle:Default:upload.html.twig', [
            'formType' => $formType,
            'form' => $form->createView(),
        ]);
    }

    private function getForm($fileType, Image $image)
    {
        return $this->createFormBuilder($image)
            ->add('title', Type\TextType::class)
            ->add('imageFile', VichType\VichImageType::class)
            ->add('save', Type\SubmitType::class)
            ->getForm()
        ;
    }

    private function getImage($imageId = null)
    {
        if (null === $imageId) {
            return new Image();
        }
        $image = $this->getDoctrine()->getRepository('VichTestBundle:Image')->find($imageId);

        return $image;
    }
}
