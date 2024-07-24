<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

class ExceptionListener
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Vérifier si l'exception est une 404 Not Found
        if ($exception instanceof NotFoundHttpException) {
            $response = new Response(
                $this->twig->render('error/404.html.twig', ['message' => $exception->getMessage()]),
                Response::HTTP_NOT_FOUND
            );

            $event->setResponse($response);
        }
    }
}
