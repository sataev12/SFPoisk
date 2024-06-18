<?php

namespace App\Controller;

use App\Entity\Rating;
use App\Entity\User;
use App\Form\RatingType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/user/{id}', name: 'app_user')]
    public function index(User $user, UserRepository $userRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $averageRating = $userRepository->getAverageRating($user);
        $rating = new Rating();
       
        return $this->render('user/index.html.twig', [
            'user' => $user,
            'averageRating' => $averageRating,
        ]);
    }

    #[Route('/user/rate/{id}', name: 'user_rate')]
    public function rate(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $rating = new Rating();
        $form = $this->createForm(RatingType::class, $rating);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rating->setUser($user);
            $entityManager->persist($rating);
            $entityManager->flush();

            return $this->redirectToRoute('app_user', ['id' => $user->getId()]);
        }

        return $this->render('user/rate.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    
}
