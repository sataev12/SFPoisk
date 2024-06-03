<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Commentaire;
use App\Form\AnnonceType;
use App\Form\CommentaireType;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\HttpCache\ResponseCacheStrategy;

class AnnonceController extends AbstractController
{
    #[Route('/annonce', name: 'app_annonce')]
    public function index(AnnonceRepository $annonceRepository): Response
    {
        // $annonces = $entityManager->getRepository(Annonce::class)->findAll();
        // $annonces = $annonceRepository->findAll();
        $annonces = $annonceRepository->findBy([], ["dateCreation" => "DESC"]);
        return $this->render('annonce/index.html.twig', [
            'annonces' => $annonces
        ]);
    }

    #[Route('/annonce/new', name: 'new_annonce')]
    #[Route('/annonce/{id}/edit', name: 'edit_annonce')]
    public function new_edit(Annonce $annonce = null, Request $request, EntityManagerInterface $entityManager): Response
    {

        if(!$annonce){
            $annonce = new Annonce();
        }
        
        $annonce->setDateCreation(new \DateTime()); // Régler automatiquement la date de création à aujourd'hui
        $form = $this->createForm(AnnonceType::class, $annonce);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $annonce = $form->getData();
            // Prepare PDO
            $entityManager->persist($annonce);

            // Gerer le téléchargement des images
            $imageFiles = $form->get('images')->getData();


            // execute PDO
            $entityManager->flush();

            return $this->redirectToRoute('app_annonce');
        }

        return $this->render('annonce/new.html.twig', [
            'formAddAnnonce' => $form,
            'edit' => $annonce->getId()
        ]);
    }

    #[Route('/annonce/{id}', name: 'show_annonce')]
    public function show(Annonce $annonce, Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentaires = $annonce->getCommentaire();

        // Créer et gérer le formulaire de commentaire
        $commentaire = new Commentaire();
        $commentaire->setAnnonce($annonce);// Associer le commentaire à l'annonce
        $commentaire->setDateCreation(new \DateTime()); // Régler automatiquement la date de création à aujourd'hui

        $form = $this->createForm(CommentaireType::class, $commentaire);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commentaire);
            $entityManager->flush();

            return $this->redirectToRoute('show_annonce', ['id' => $annonce->getId()]);
        }
        return $this->render('annonce/show.html.twig', [
            'annonce' => $annonce,
            'commentaires' => $commentaires,
            'formCommentaire' => $form->createView(),
        ]);
    }

}
