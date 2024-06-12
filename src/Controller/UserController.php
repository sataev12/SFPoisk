<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/user/{id}', name: 'app_user')]
    public function index(User $user): Response
    {

        return $this->render('user/index.html.twig', [
            'user' => $user,
        ]);
    }

    // $annonces = $annonceRepository->findBy([], ["dateCreation" => "DESC"]);
    //     return $this->render('annonce/index.html.twig', [
    //         'annonces' => $annonces
    //     ]);
}
