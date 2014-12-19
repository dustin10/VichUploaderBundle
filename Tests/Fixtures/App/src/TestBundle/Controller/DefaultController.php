<?php

namespace Vich\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Vich\TestBundle\Entity\Image;

class DefaultController extends Controller
{
    public function uploadAction($formType)
    {
        $form = $this->getForm($formType, $this->getImage());

        return $this->render('VichTestBundle:Default:upload.html.twig', array(
            'formType'  => $formType,
            'form'      => $form->createView(),
        ));
    }

    public function editAction($formType, $imageId)
    {
        $form = $this->getForm($formType, $this->getImage($imageId));

        return $this->render('VichTestBundle:Default:edit.html.twig', array(
            'imageId'   => $imageId,
            'formType'  => $formType,
            'form'      => $form->createView(),
        ));
    }

    public function submitAction(Request $request, $formType, $imageId = null)
    {
        $image = $this->getImage($imageId);
        $form = $this->getForm($formType, $image);

        $form->submit($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($image);
            $em->flush();

            return $this->redirect($this->generateUrl('view', array(
                'formType' => $formType,
                'imageId'  => $image->getId()
            )));
        }

        return $this->render('VichTestBundle:Default:upload.html.twig', array(
            'formType'  => $formType,
            'form'      => $form->createView(),
        ));
    }

    private function getForm($fileType, Image $image)
    {
        return $this->createFormBuilder($image)
            ->add('title', 'text')
            ->add('imageFile', $fileType)
            ->add('save', 'submit')
            ->getForm();
    }

    private function getImage($imageId = null)
    {
        if ($imageId === null) {
            return new Image();
        }

        $em = $this->getDoctrine()->getManager();
        $image = $em->getRepository('VichTestBundle:Image')->find($imageId);

        return $image;
    }
}
