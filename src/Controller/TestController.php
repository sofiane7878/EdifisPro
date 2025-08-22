<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test')]
    public function test(): Response
    {
        return new Response('
            <html>
                <head><title>Test - EdifisPro</title></head>
                <body>
                    <h1>✅ Application Symfony fonctionne !</h1>
                    <p>Si vous voyez ce message, Symfony fonctionne correctement.</p>
                    <p>Le problème est probablement dans la configuration de sécurité ou la base de données.</p>
                    <hr>
                    <h2>Informations système :</h2>
                    <ul>
                        <li>PHP Version : ' . phpversion() . '</li>
                        <li>Symfony Version : ' . \Symfony\Component\HttpKernel\Kernel::VERSION . '</li>
                        <li>Environment : ' . $this->getParameter('kernel.environment') . '</li>
                    </ul>
                    <hr>
                    <p><a href="/login">Aller à la page de connexion</a></p>
                </body>
            </html>
        ');
    }

    #[Route('/health', name: 'app_health')]
    public function health(): Response
    {
        return new Response('OK', 200, ['Content-Type' => 'text/plain']);
    }
}
