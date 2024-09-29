<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MessageController extends AbstractController
{   
    // Méthode pour afficher la liste des messages reçus
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
        $currentUser = $this->getUser();
        // Vérifie si l'utilisateur actuel essaie de s'envoyer un message
        if ($currentUser && $currentUser->getId() === $user->getId()) {
            $this->addFlash('error', 'vous ne pouvez pas vous envoyer un message.');
            return $this->redirectToRoute('app_annonce'); //Rediriger vers une autre page
        }
        $message = new Message();// Crée un nouvel objet Message
        $message->setDestinataire($user);// Associe le destinataire au message
        $form = $this->createForm(MessageType::class, $message);//Crée un formulaire pour le msg avec le type MessageType
        $form->handleRequest($request);// Gère la requête du formulaire
        if($form->isSubmitted() && $form->isValid()) {// Vérifie si le formulaire est soumis et valide
            // Associer l'utilisateur connecté comme expéditeur
            $message->setExpediteur($this->getUser()); // ça je vais refaire quand je vais finir authentification
            $message->setDateEnvoi(new \DateTime());// Régler automatiquement la date d'envoi
            $userDestinataire = $message->getDestinataire();// Récupère le destinataire du message
            // Incrémente le compteur de nouveaux messages pour le destinataire
            $userDestinataire->setNouveauxMessages($userDestinataire->getNouveauxMessages() + 1);
            // Persiste les modifications pour le destinataire et le message dans la base de données
            $entityManager->persist($userDestinataire);
            $entityManager->persist($message);
            $entityManager->flush();// Sauvegarde les changements dans la base de données

            return $this->redirectToRoute('received_messages');
        }
        return $this->render('message/new.html.twig', [
            'formMessage' => $form->createView(),
        ]);
    }

    // Méthode pour afficher les messages reçus
    #[Route('/messages/recus', name: 'received_messages')]
    public function receivedMessages(EntityManagerInterface $entityManager, MessageRepository $messageRepository, UserRepository $userRepository): Response
    {
        $user = $this->getUser();// Récupère l'utilisateur actuellement connecté
        // Récupère les messages reçus par l'utilisateur connecté, triés par date d'envoi
        $messages = $messageRepository->findBy(['destinataire' => $user], ['dateEnvoi' => 'DESC']);
        $userRepository->updateNewMessages($user);// Met à jour le nombre de nouveaux messages pour l'utilisateur
        $entityManager->persist($user); // Persiste les modifications de l'utilisateur dans la base de données
        $entityManager->flush();
        return $this->render('message/received.html.twig', [
            'messages' => $messages,
            
        ]);
    }

    // Méthode pour afficher les messages reçus
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

    // Méthode pour afficher les messages envoyés par l'utilisateur
    #[Route('/messages/user/{id}', name: 'messages_user')]
    public function messagesByUser(MessageRepository $messageRepository): Response
    {
        $user = $this->getUser();// Récupère l'utilisateur actuellement connecté
        // Récupère les messages envoyés par l'utilisateur connecté, triés par date d'envoi
        $messages = $messageRepository->findBy(['expediteur' => $user], ['dateEnvoi' => 'DESC']);

        return $this->render('message/user_messages.html.twig', [
            'messages' => $messages,
            'user' => $user
            ]);
        }
    

    #[Route('/messages/lire/{id}', name: 'read_messages')]
    public function readMessage(Message $message, EntityManagerInterface $entityManager): Response
    {
        $message->setLu(true); // Marque le message comme lu en définissant l'attribut 'lu' à true
        $entityManager->flush(); // Sauvegarde les changements dans la base de données

        return $this->redirectToRoute('received_messages');
    }
}