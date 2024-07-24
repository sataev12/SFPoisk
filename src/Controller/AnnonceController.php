<?php

namespace App\Controller;


use App\Entity\Photo;
use App\Entity\Annonce;
use App\Entity\Categorie;
use App\Form\AnnonceType;
use App\Entity\Commentaire;
use App\Entity\Favoris;
use App\Entity\Signalement;
use App\Form\RechercheType;
use App\Form\CommentaireType;
use App\Form\SignalementType;
use App\Repository\AnnonceRepository;
use App\Repository\CategorieRepository;
use App\Repository\FavorisRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SignalementRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\HttpCache\ResponseCacheStrategy;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AnnonceController extends AbstractController
{

#[Route('/annonce', name: 'app_annonce')]
public function index(Request $request, AnnonceRepository $annonceRepository,  CategorieRepository $categorieRepository,  PaginatorInterface $paginator,  FavorisRepository $favorisRepository): Response 
{
    $form = $this->createForm(RechercheType::class);
    $form->handleRequest($request);

    $keyword = $form->get('keyword')->getData();
    $ville = $form->get('ville')->getData();
    $minPrix = $form->get('minPrix')->getData();
    $maxPrix = $form->get('maxPrix')->getData();
    
    $query = $keyword ? $annonceRepository->rechercheAnnonce($keyword, $ville, $minPrix, $maxPrix) : $annonceRepository->findBy([], ['dateCreation' => 'DESC']);

    $annonces = $paginator->paginate(
        $query, // Query or array
        $request->query->getInt('page', 1), // Current page number, default to 1
        10 // Items per page
    );

    $user = $this->getUser();
    $favorisAnnoncesIds = [];

    if ($user) {
        $favoris = $favorisRepository->findBy(['user' => $user]);
        foreach ($favoris as $favori) {
            $favorisAnnoncesIds[] = $favori->getAnnonce()->getId();
        }
    }

    $categories = $categorieRepository->findAll();

    return $this->render('annonce/index.html.twig', [
        'annonces' => $annonces,
        'RechercheForm' => $form->createView(),
        'favorisAnnoncesIds' => $favorisAnnoncesIds,
        'categories' => $categories,
    ]);
}

    // Afficher les annonces par catégories
    #[Route('/categorie/{id}', name: 'annonces_par_categorie')]
    public function annoncesParCategorie(Request $request, Categorie $categorie, AnnonceRepository $annonceRepository, CategorieRepository $categorieRepository, SessionInterface $session): Response
    {
        $annonces = $annonceRepository->findBy(['categorie' => $categorie], ['dateCreation' => 'DESC']);
        $categories = $categorieRepository->findAll();

       
        

        return $this->render('annonce/annonce_par_categorie.html.twig', [
            'annonces' => $annonces,
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
    public function show(int $id, Annonce $annonce, Request $request, EntityManagerInterface $entityManager): Response
    {
        // $annonce = $entityManager->getRepository(Annonce::class)->find($id);

        // if (!$annonce) {
            
        //     throw $this->createNotFoundException('Cette annonce n\'existe pas.');
        // }

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

    // La partie dedie pour ajout/suppression des favoris
    #[Route('/annonce/{id}/favori', name: 'add_favori')]
    public function addFavori(Annonce $annonce, EntityManagerInterface $entityManager, FavorisRepository $favorisRepository): Response
    {
        $user = $this->getUser();
        $favori = $favorisRepository->findOneBy(['user' => $user, 'annonce' => $annonce]);

        // Ajoutez l'annonce aux favoris si elle n'y est pas déjà
        if(!$favori) {
            $favori = new Favoris();
            $favori->setUser($user);
            $favori->setAnnonce($annonce);
            $entityManager->persist($favori);
            $entityManager->flush();
        }

        return $this->redirectToRoute('show_annonce', ['id' => $annonce->getId()]);
    }

    // Ajoutez cette méthode pour afficher les favoris
    #[Route('/favoris', name: 'view_favoris')]
    public function viewFavoris(FavorisRepository $favorisRepository): Response
    {
        $user = $this->getUser();
        $favoris = $favorisRepository->findBy(['user' => $user]);
        

        return $this->render('annonce/favoris.html.twig', [
            'favoris' => $favoris,
        ]);
    }

    // Suppression de favoris
    #[Route('/annonce/{id}/favori/remove', name: 'remove_favori')]
    public function removeFavori(Annonce $annonce, EntityManagerInterface $entityManager, FavorisRepository $favorisRepository): Response
    {
        $user = $this->getUser();
        $favori = $favorisRepository->findOneBy(['user' => $user, 'annonce' => $annonce]);

        if ($favori) {
            $entityManager->remove($favori);
            $entityManager->flush();
        }

        return $this->redirectToRoute('view_favoris');
    }

    
    // Creer une methode pour signaler l'annonce
    #[Route('/annonce/{id}/signaler', name: 'signaler_annonce')]
    public function signaleAnnonce(Annonce $annonce,Request $request, EntityManagerInterface $entityManager): Response
    {

        $signalement = new Signalement();
        $signalement->setAnnonce($annonce);
        $signalement->setUser($this->getUser());
        $signalement->setDateSignalement(new \DateTime());
        

        $formSignalement = $this->createForm(SignalementType::class, $signalement);
        $formSignalement->handleRequest($request);

        if($formSignalement->isSubmitted() && $formSignalement->isValid()) {
            $entityManager->persist($signalement);
            $entityManager->flush();

            $this->addFlash('success', 'L\'annonce a été signalé avec succès.');
            return $this->redirectToRoute('show_annonce', ['id' => $annonce->getId()]);
        }

        return $this->render('annonce/signaler.html.twig', [
            'annonce' => $annonce,
            'formSignalement' => $formSignalement->createView(),
        ]);
    }

    // Afficher la liste des signalements
    #[Route('/signalements', name: 'liste_signalements')]
    public function listeSignalements(SignalementRepository $signalementRepository): Response
    {
        $signalements = $signalementRepository->findAll();

        return $this->render('annonce/liste_signalements.html.twig', [
            'signalements' => $signalements,
        ]);
    }

}