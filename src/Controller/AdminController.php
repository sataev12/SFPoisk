<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AdminController extends AbstractController
{
    #[Route('/admin/categorie/new', name: 'admin_categorie_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function newCategorie(Request $request, EntityManagerInterface $entityManager): Response
    {

        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorie);
            $entityManager->flush();

            return $this->redirectToRoute('admin_categorie_list');
        }
        return $this->render('admin/new_categorie.html.twig', [
            'form' => $form->createView(),
        ]);


    }

    #[Route('/admin/categories', name: 'admin_categorie_list')]
    #[IsGranted('ROLE_ADMIN')]
    public function listCategories(CategorieRepository $categorieRepository): Response
    {
        $categories = $categorieRepository->findAll();

        return $this->render('admin/list_categories.html.twig', [
            'categories' => $categories,
        ]);
    }

    // Pour changer la catégorie
    #[Route('/admin/categorie/{id}/edit', name: 'admin_categorie_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function editCategorie(Categorie $categorie, Request $request, EntityManagerInterface $entityManager): Response
    {

        $form = $this->createForm(CategorieType::class, $categorie);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            return $this->redirectToRoute('admin_categorie_list');
        }

        return $this->render('admin/edit_categorie.html.twig', [
            'form' => $form->createView(),
            'categorie' => $categorie,
        ]);
    }

    // Pour la suppression des catégories
    #[Route('/admin/categorie/{id}/delete', name: 'admin_categorie_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteCategorie(Categorie $categorie, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {

        if($categorie->getAnnonce()->count() > 0) {
            $this->addFlash('error', 'Cette catégorie contient des annonces. Supprimer d\'abord les annonces associes.');
            return $this->redirectToRoute('admin_categorie_list');
        }

        $entityManager->remove($categorie);
        $entityManager->flush();

        $this->addFlash('success', 'Catégorie supprimé avec succès.');
        return $this->redirectToRoute('admin_categorie_list');
    }
}
