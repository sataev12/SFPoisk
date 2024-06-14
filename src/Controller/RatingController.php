<?php

namespace App\Controller;

use App\Entity\Rating;
use App\Entity\User;
use App\Form\RatingType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RatingController extends AbstractController
{
    #[Route('/rating', name: 'app_rating')]
    public function rate(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {

        $rating = new Rating();
        $form = $this->createForm(RatingType::class, $rating);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            // Enregistrer la note dans la base de données
            $entityManager->persist($rating);
            $entityManager->flush();

            
            return $this->redirectToRoute('app_user');
        }
        return $this->render('user/index.html.twig', [
            'form' => $form->createView(),
            
        ]);
    }
}
