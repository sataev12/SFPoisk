<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentaireController extends AbstractController
{
    #[Route('/commentaire', name: 'app_commentaire')]
    public function index(): Response
    {
        return $this->render('commentaire/index.html.twig', [
            'controller_name' => 'CommentaireController',
        ]);
    }


    // Creation d'une methode pour modifier les commentaires
    #[Route('/commentaire/{id}/edit', name: 'edit_commentaire')]
    public function edit(Commentaire $commentaire, Request $request, EntityManagerInterface $entityManager): Response
    {
        
        $commentaire->setDateCreation(new \DateTime()); // Régler automatiquement la date de création à aujourd'hui

        $form = $this->createForm(CommentaireType::class, $commentaire);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('show_annonce', ['id' => $commentaire->getAnnonce()->getId()]);
        }
        return $this->render('commentaire/edit.html.twig', [
            'formCommentaire' => $form->createView(),
        ]);
    }


    #[Route('/commentaire/{id}/delete', name: 'delete_commentaire')]
    public function delete(Commentaire $commentaire, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($commentaire);
        $entityManager->flush();

        return $this->redirectToRoute('show_annonce', ['id' => $commentaire->getAnnonce()->getId()]);
    }

}
