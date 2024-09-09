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
use App\Repository\CommentaireRepository;
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
public function index(Request $request, AnnonceRepository $annonceRepository,  CategorieRepository $categorieRepository,  PaginatorInterface $paginator,  FavorisRepository $favorisRepository, CommentaireRepository $commentaireRepository): Response 
{
    $form = $this->createForm(RechercheType::class);
    $form->handleRequest($request);

    $keyword = $form->get('keyword')->getData();
    $ville = $form->get('ville')->getData();
    $minPrix = $form->get('minPrix')->getData();
    $maxPrix = $form->get('maxPrix')->getData();
    
    $query = $annonceRepository->rechercheAnnonce($keyword, $ville, $minPrix, $maxPrix);

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
    $derniersCommentaires = $commentaireRepository->findBy([], ['dateCreation' => 'DESC'], 5); // Récupérer les 5 derniers commentaires

    return $this->render('annonce/index.html.twig', [
        'annonces' => $annonces,
        'RechercheForm' => $form->createView(),
        'favorisAnnoncesIds' => $favorisAnnoncesIds,
        'categories' => $categories,
        'derniersCommentaires' => $derniersCommentaires,
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
// Ces deux annotations de route définissent deux URL différentes pour la même action du contrôleur : 
// une pour créer une nouvelle annonce (/annonce/new) et une autre pour éditer une annonce existante (/annonce/{id}/edit).

public function new_edit(Annonce $annonce = null, Request $request, EntityManagerInterface $entityManager): Response
{
    // Cette fonction accepte une entité Annonce (qui peut être null si aucune annonce n'existe),
    // une requête HTTP, et l'EntityManager pour la gestion des entités.

    if(!$annonce){
        $annonce = new Annonce();
        // Si aucune annonce n'est passée à la fonction (donc si $annonce est null), 
        // une nouvelle instance de l'entité Annonce est créée.

        $annonce->setPublier($this->getUser());
        // Définir l'utilisateur actuellement connecté comme auteur de l'annonce.
    }
    
    $annonce->setDateCreation(new \DateTime());
    // Met à jour la date de création de l'annonce avec la date actuelle.

    $form = $this->createForm(AnnonceType::class, $annonce);
    // Crée un formulaire basé sur la classe AnnonceType, prérempli avec les données de l'entité Annonce.

    $form->handleRequest($request);
    // Traite la requête HTTP et remplit le formulaire avec les données soumises.

    if($form->isSubmitted() && $form->isValid()) {
        // Vérifie si le formulaire a été soumis et s'il est valide.
        
        $annonce = $form->getData();
        // Récupère les données de l'entité Annonce à partir du formulaire.

        $entityManager->persist($annonce);
        // Prépare l'entité Annonce pour être sauvegardée dans la base de données.

        $imageFiles = $form->get('images')->getData();
        // Récupère les fichiers d'image téléchargés à partir du formulaire.

        foreach($imageFiles as $imageFile) {
            // Parcourt chaque fichier d'image soumis dans le formulaire.

            $originalFileName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            // Récupère le nom de fichier original sans extension.

            $newFileName = uniqid() . '.' . $imageFile->guessExtension();
            // Génère un nom de fichier unique en utilisant uniqid() et conserve l'extension d'origine.

            try {
                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFileName
                );
                // Déplace le fichier téléchargé dans le répertoire désigné pour les images (images_directory) 
                // avec le nouveau nom de fichier.

            } catch (FileException $e) {
                // Capture toute exception pouvant survenir lors du téléchargement du fichier et traite l'erreur.
            }

            $photo = new Photo();
            // Crée une nouvelle instance de l'entité Photo.

            $photo->setUrl('/img/' . $newFileName);
            // Définit l'URL de la photo basée sur le chemin relatif et le nouveau nom de fichier.

            $photo->setAnnonce($annonce);
            // Associe la photo à l'annonce actuelle.

            $entityManager->persist($photo);
            // Prépare l'entité Photo pour être sauvegardée dans la base de données.
        }

        $entityManager->flush();
        // Exécute toutes les opérations de sauvegarde en attente (persist) dans la base de données.

        return $this->redirectToRoute('app_annonce');
        // Redirige l'utilisateur vers la route 'app_annonce' après la soumission et la sauvegarde de l'annonce.
    }

    return $this->render('annonce/new.html.twig', [
        'formAddAnnonce' => $form,
        'edit' => $annonce->getId()
    ]);
    // Affiche la vue Twig 'annonce/new.html.twig', en passant le formulaire à la vue 
    // ainsi que l'identifiant de l'annonce si elle est en mode édition.
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

        // Incrémenter le comteur de vues
        $annonce->setVues();
        $entityManager->flush();

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