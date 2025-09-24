<?php

namespace Vich\TestBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vich\TestBundle\Entity\ValidatedImage;
use Vich\TestBundle\Form\ValidatedImageType;

final class ValidatorTestController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $doctrine)
    {
    }

    public function uploadAction(Request $request): Response
    {
        $validatedImage = new ValidatedImage();
        $form = $this->createForm(ValidatedImageType::class, $validatedImage);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($validatedImage);
            $entityManager->flush();

            return $this->redirectToRoute('validator_test_view', ['id' => $validatedImage->getId()]);
        }

        return $this->render('validator_test/upload.html.twig', [
            'form' => $form->createView(),
            'validatedImage' => $validatedImage,
        ]);
    }

    public function editAction(Request $request, int $id): Response
    {
        $validatedImage = $this->getValidatedImage($id);
        $form = $this->createForm(ValidatedImageType::class, $validatedImage);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->doctrine->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('validator_test_view', ['id' => $validatedImage->getId()]);
        }

        return $this->render('validator_test/edit.html.twig', [
            'form' => $form->createView(),
            'validatedImage' => $validatedImage,
        ]);
    }

    public function viewAction(int $id): Response
    {
        $validatedImage = $this->getValidatedImage($id);

        return $this->render('validator_test/view.html.twig', [
            'validatedImage' => $validatedImage,
        ]);
    }

    private function getValidatedImage(int $id): ValidatedImage
    {
        $repository = $this->doctrine->getRepository(ValidatedImage::class);
        $validatedImage = $repository->find($id);

        if (!$validatedImage) {
            throw $this->createNotFoundException('ValidatedImage not found');
        }

        return $validatedImage;
    }
}
