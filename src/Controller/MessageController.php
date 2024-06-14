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
        $user = $this->getUser();

        $messages = $messageRepository->findBy(['destinataire' => $user], ["dateEnvoi" => "DESC"]);
        return $this->render('message/index.html.twig', [
            'messages' => $messages
        ]);
    }

    // Mise à jour du contrôleur pour ajouter une action new pour l'envoi de message
    #[Route('/message/{id}/new', name: 'new_message')]
    public function new(Request $request, EntityManagerInterface $entityManager, User $user): Response
    {

        $message = new Message();
        $message->setDestinataire($user);
        $form = $this->createForm(MessageType::class, $message);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            // Associer l'utilisateur connecté comme expéditeur
            $message->setExpediteur($this->getUser()); // ça je vais refaire quand je vais finir authentification
            // Régler automatiquement la date d'envoi
            $message->setDateEnvoi(new \DateTime());

            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('received_messages');
        }

        return $this->render('message/new.html.twig', [
            'formMessage' => $form->createView(),
        ]);
    }

    #[Route('/messages/recus', name: 'received_messages')]
    public function receivedMessages(MessageRepository $messageRepository): Response
    {
        $user = $this->getUser();
        $messages = $messageRepository->findBy(['destinataire' => $user], ['dateEnvoi' => 'DESC']);

        return $this->render('message/received.html.twig', [
            'messages' => $messages,
        ]);
    }

     #[Route('/messages/sent', name: 'app_message_sent')]
    public function envoyee(MessageRepository $messageRepository): Response
    {
        //Recuperer l'utilisateur connecté
        $user = $this->getUser();

        // Récupérer les messages envoyés par l'utilisateur connecté
        $messageEnvoye = $messageRepository->findBy(['expediteur' => $user]);

        return $this->render('message/sent.html.twig', [
            'messageEnvoye' => $messageEnvoye,
        ]);
    }

    #[Route('/messages/user/{id}', name: 'messages_user')]
    public function messagesByUser(MessageRepository $messageRepository): Response
    {
        $user = $this->getUser();
        $messages = $messageRepository->findBy(['expediteur' => $user], ['dateEnvoi' => 'DESC']);

        return $this->render('message/user_messages.html.twig', [
            'messages' => $messages,
            'user' => $user
            ]);
        }
    

    #[Route('/messages/lire/{id}', name: 'read_messages')]
    public function readMessage(Message $message, EntityManagerInterface $entityManager): Response
    {
        $message->setLu(true);
        $entityManager->flush();

        return $this->redirectToRoute('received_messages');
    }
}
