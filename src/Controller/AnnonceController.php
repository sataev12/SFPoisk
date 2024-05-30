<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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

    #[Route('/annonce/{id}', name: 'show_annonce')]
    public function show(Annonce $annonce): Response
    {
        return $this->render('annonce/show.html.twig', [
            'annonce' => $annonce
        ]);
    }
}
