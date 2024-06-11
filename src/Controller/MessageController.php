<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MessageController extends AbstractController
{
    #[Route('/message', name: 'app_message')]
    public function index(MessageRepository $messageRepository): Response
    {
        $messages = $messageRepository->findBy([], ["dateEnvoi" => "DESC"]);
        return $this->render('message/index.html.twig', [
            'messages' => $messages
        ]);
    }

    // Mise à jour du contrôleur pour ajouter une action new pour l'envoi de message
    #[Route('/message/new/{destinateure}', name: 'new_message')]
    public function new(Request $request, EntityManagerInterface $entityManager, User $destinataire): Response
    {

        $message = new Message();
        $message->setExpediteur($this->getUser());
        $form = $this->createForm(MessageType::class, $message);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            // Associer l'utilisateur connecté comme expéditeur
            //ça je vais refaire quand je vais finir authentification
            // Régler automatiquement la date d'envoi
            $message->setDateEnvoi(new \DateTime());

            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('app_message');
        }

        return $this->render('message/new.html.twig', [
            'formMessage' => $form->createView(),
        ]);
    }
}
