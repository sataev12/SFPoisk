<?php

namespace App\Controller;

use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
