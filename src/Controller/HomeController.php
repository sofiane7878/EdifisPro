<?php

namespace App\Controller;

use App\Repository\EquipeRepository;
use App\Repository\ChantierRepository;
use App\Repository\OuvrierRepository;
use App\Repository\AffectationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
        
        return $this->redirectToRoute('app_login');
    }

    #[Route('/home', name: 'app_home')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function home(
        EquipeRepository $equipeRepository,
        ChantierRepository $chantierRepository,
        OuvrierRepository $ouvrierRepository,
        AffectationRepository $affectationRepository
    ): Response {
        // Rediriger selon le rôle de l'utilisateur
        if ($this->isGranted('ROLE_OUVRIER')) {
            return $this->redirectToRoute('app_ouvrier_dashboard');
        }

        // Pour les admins, afficher le dashboard normal
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'equipes_count' => $equipeRepository->count([]),
            'chantiers_count' => $chantierRepository->count([]),
            'ouvriers_count' => $ouvrierRepository->count([]),
            'affectations_count' => $affectationRepository->count([]),
        ]);
    }
}
