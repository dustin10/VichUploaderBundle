<?php

namespace Vich\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Vich\TestBundle\Entity\Image;

class DefaultController extends Controller
{
    public function uploadAction()
    {
        $form = $this->getForm(new Image());

        return $this->render('VichTestBundle:Default:upload.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function submitAction(Request $request)
    {
        $image = new Image();
        $form = $this->getForm($image);

        $form->submit($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($image);
            $em->flush();
        }

        return $this->render('VichTestBundle:Default:upload.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    private function getForm(Image $image)
    {
        return $this->createFormBuilder($image)
            ->add('title', 'text')
            ->add('imageFile', 'file')
            ->add('save', 'submit')
            ->getForm();
    }
}
