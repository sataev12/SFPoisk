<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Categorie;
use App\Entity\Commentaire;
use App\Entity\Photo;
use App\Entity\Signalement;
use App\Form\AnnonceType;
use App\Form\CommentaireType;
use App\Form\RechercheType;
use App\Repository\AnnonceRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\HttpCache\ResponseCacheStrategy;

class AnnonceController extends AbstractController
{
    #[Route('/annonce', name: 'app_annonce')]
    public function index(Request $request, AnnonceRepository $annonceRepository, CategorieRepository $categorieRepository , SessionInterface $session): Response
    {
        // $annonces = $entityManager->getRepository(Annonce::class)->findAll();
        // $annonces = $annonceRepository->findAll();
        

        $form = $this->createForm(RechercheType::class);
        $form->handleRequest($request);

        $keyword = $form->get('keyword')->getData();
        $ville = $form->get('ville')->getData();
        $minPrix = $form->get('minPrix')->getData();
        $maxPrix = $form->get('maxPrix')->getData();
        $annonces = $keyword ? $annonceRepository->rechercheAnnonce($keyword, $ville, $minPrix, $maxPrix) : $annonceRepository->findBy([], ['dateCreation' => 'DESC']);
        
        $categories = $categorieRepository->findAll();
        return $this->render('annonce/index.html.twig', [
            'annonces' => $annonces,
            'RechercheForm' => $form->createView(),
            'favoris' => $session->get('favoris', []),
            'categories' => $categories,
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


    #[Route('/annonce/{id}/favori', name: 'add_favori')]
    public function addFavori(Annonce $annonce, SessionInterface $session): Response
    {
        $favoris = $session->get('favoris', []);

        // Ajoutez l'annonce aux favoris si elle n'y est pas déjà
        if(!in_array($annonce->getId(), $favoris)) {
            $favoris[] = $annonce->getId();
        }

        $session->set('favoris', $favoris);

        return $this->redirectToRoute('show_annonce', ['id' => $annonce->getId()]);
    }

    // Ajoutez cette méthode pour afficher les favoris
    #[Route('/favoris', name: 'view_favoris')]
    public function viewFavoris(SessionInterface $session, AnnonceRepository $annonceRepository): Response
    {
        $favoris = $session->get('favoris', []);
        $annonces = $annonceRepository->findBy(['id' => $favoris]);

        return $this->render('annonce/favoris.html.twig', [
            'annonces' => $annonces,
        ]);
    }

    // Suppression de favoris
    #[Route('/annonce/{id}/favori/remove', name: 'remove_favori')]
    public function removeFavori(Annonce $annonce, SessionInterface $session): Response
    {
        $favoris = $session->get('favoris', []);

        // Supprimer l'annonce des favoris si elle y est
        if(in_array($annonce->getId(), $favoris)) {
            $favoris = array_diff($favoris, [$annonce->getId()]);
        }

        $session->set('favoris', $favoris);

        return $this->redirectToRoute('view_favoris');
    }

    #[Route('/annonce/{id}/signaler', name: 'signaler_annonce')]
    public function signalerAnnonce(Annonce $annonce, Request $request, EntityManagerInterface $entityManager): Response
    {
        $signalement = new Signalement();
        $signalement->setAnnonce($annonce);
        $signalement->setUser($this->getUser());
        $signalement->setDateSignalement(new \DateTime());
        
        $form = $this->createForm(SignalementType::class, $signalement);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($signalement);
            $entityManager->flush();

            $this->addFlash('success', 'Votre signalement a été enregistré.');
            return $this->redirectToRoute('show_annonce', ['id' => $annonce->getId()]);
        }
        return $this->render('annonce/signaler.html.twig', [
            'annonce' => $annonce,
            'form' => $form->createView(),
        ]);
    }

}