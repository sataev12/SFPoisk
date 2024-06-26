<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Commentaire;
use App\Entity\Photo;
use App\Form\AnnonceType;
use App\Form\CommentaireType;
use App\Form\RechercheType;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
            $annonce->setPublier($this->getUser()); // Définir l'utilisateur courant comme auteur

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

            foreach($imageFiles as $imageFile) {
                $originalFileName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFileName = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFileName
                    );
                } catch (FileException $e) {
                    // Handle exception if something happens during file upload
                }

                $photo = new Photo();
                $photo->setUrl('/img/' . $newFileName);
                $photo->setAnnonce($annonce);
                $entityManager->persist($photo);
            }


            // execute PDO
            $entityManager->flush();

            return $this->redirectToRoute('app_annonce');
        }

        return $this->render('annonce/new.html.twig', [
            'formAddAnnonce' => $form,
            'edit' => $annonce->getId()
        ]);
    }

    #[Route('/annonce/{id}/delete', name: 'delete_annonce')]
    public function delete(Annonce $annonce, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($annonce);
        $entityManager->flush();

        return $this->redirectToRoute('app_annonce');
    }


    #[Route('/annonce/{id}', name: 'show_annonce')]
    public function show(Annonce $annonce, Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentaires = $annonce->getCommentaire();

        // Créer et gérer le formulaire de commentaire
        $commentaire = new Commentaire();
        $commentaire->setUtilisateur($this->getUser()); // Définir l'utilisateur courant comme auteur
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

    #[Route('/annonce', name: 'recherche_annonce')]
    public function recherche(): Response
    {
        $annonce = new Annonce();
        $form = $this->createForm(RechercheType::class, ) 
        
    }

}
