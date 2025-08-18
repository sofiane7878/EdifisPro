<?php

namespace App\Controller;

use App\Entity\Ouvrier;
use App\Entity\User;
use App\Repository\AffectationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ouvrier-dashboard')]
#[IsGranted('ROLE_OUVRIER')]
final class OuvrierDashboardController extends AbstractController
{
    #[Route('/', name: 'app_ouvrier_dashboard', methods: ['GET'])]
    public function index(AffectationRepository $affectationRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $ouvrier = $user->getOuvrier();

        if (!$ouvrier) {
            throw $this->createAccessDeniedException('Aucun ouvrier associé à ce compte.');
        }

        // Récupérer les affectations de l'ouvrier via son équipe
        $affectations = $affectationRepository->createQueryBuilder('a')
            ->join('a.equipe', 'e')
            ->join('e.ouvriers', 'o')
            ->where('o = :ouvrier')
            ->setParameter('ouvrier', $ouvrier)
            ->getQuery()
            ->getResult();

        return $this->render('ouvrier_dashboard/index.html.twig', [
            'ouvrier' => $ouvrier,
            'affectations' => $affectations,
        ]);
    }

    #[Route('/mes-chantiers', name: 'app_ouvrier_mes_chantiers', methods: ['GET'])]
    public function mesChantiers(AffectationRepository $affectationRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $ouvrier = $user->getOuvrier();

        if (!$ouvrier) {
            throw $this->createAccessDeniedException('Aucun ouvrier associé à ce compte.');
        }

        // Récupérer les chantiers de l'ouvrier via ses affectations
        $affectations = $affectationRepository->createQueryBuilder('a')
            ->join('a.chantier', 'c')
            ->join('a.equipe', 'e')
            ->join('e.ouvriers', 'o')
            ->where('o = :ouvrier')
            ->setParameter('ouvrier', $ouvrier)
            ->getQuery()
            ->getResult();
        
        // Extraire les chantiers uniques
        $chantiers = [];
        foreach ($affectations as $affectation) {
            $chantier = $affectation->getChantier();
            if ($chantier && !in_array($chantier, $chantiers)) {
                $chantiers[] = $chantier;
            }
        }

        return $this->render('ouvrier_dashboard/mes_chantiers.html.twig', [
            'ouvrier' => $ouvrier,
            'chantiers' => $chantiers,
        ]);
    }

    #[Route('/mon-profil', name: 'app_ouvrier_profil', methods: ['GET'])]
    public function profil(AffectationRepository $affectationRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $ouvrier = $user->getOuvrier();

        if (!$ouvrier) {
            throw $this->createAccessDeniedException('Aucun ouvrier associé à ce compte.');
        }

        // Récupérer les affectations de l'ouvrier via son équipe
        $affectations = $affectationRepository->createQueryBuilder('a')
            ->join('a.equipe', 'e')
            ->join('e.ouvriers', 'o')
            ->where('o = :ouvrier')
            ->setParameter('ouvrier', $ouvrier)
            ->getQuery()
            ->getResult();

        return $this->render('ouvrier_dashboard/profil.html.twig', [
            'ouvrier' => $ouvrier,
            'affectations' => $affectations,
        ]);
    }
} 