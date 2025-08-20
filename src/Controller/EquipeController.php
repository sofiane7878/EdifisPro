<?php

namespace App\Controller;

use App\Entity\Equipe;
use App\Form\EquipeType;
use App\Repository\EquipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/equipe')]
#[IsGranted('ROLE_ADMIN')]

final class EquipeController extends AbstractController
{
    #[Route(name: 'app_equipe_index', methods: ['GET'])]
    public function index(EquipeRepository $equipeRepository): Response
    {
        return $this->render('equipe/index.html.twig', [
            'equipes' => $equipeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_equipe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $equipe = new Equipe();
        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ouvriersSelectionnes = $form->get('ouvriers')->getData();
            $equipe->setNombre(count($ouvriersSelectionnes));

            $entityManager->persist($equipe);

            // Ajouter les ouvriers sélectionnés à l'équipe
            foreach ($ouvriersSelectionnes as $ouvrier) {
                $ouvrier->setEquipe($equipe);
                $entityManager->persist($ouvrier);
            }

            // Mettre à jour la relation du chef d'équipe
            if ($equipe->getChefEquipe()) {
                $chef = $equipe->getChefEquipe();
                $chef->setEquipe($equipe);
                $entityManager->persist($chef);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Équipe créée avec succès.');
            return $this->redirectToRoute('app_equipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('equipe/new.html.twig', [
            'equipe' => $equipe,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_equipe_show', methods: ['GET'])]
    public function show(Equipe $equipe): Response
    {
        return $this->render('equipe/show.html.twig', [
            'equipe' => $equipe,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_equipe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Equipe $equipe, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        // Debug: Vérifier si le formulaire est soumis
        if ($form->isSubmitted()) {
            $this->addFlash('info', 'Formulaire soumis');
            
            if ($form->isValid()) {
                $this->addFlash('info', 'Formulaire valide - traitement en cours...');
                
                $ouvriersSelectionnes = $form->get('ouvriers')->getData();
                $equipe->setNombre(count($ouvriersSelectionnes));

                // D'abord, retirer tous les ouvriers de cette équipe
                $ouvriersActuels = $equipe->getOuvriers();
                foreach ($ouvriersActuels as $ouvrier) {
                    $ouvrier->setEquipe(null);
                    $entityManager->persist($ouvrier);
                }

                // Ensuite, ajouter les nouveaux ouvriers sélectionnés
                foreach ($ouvriersSelectionnes as $ouvrier) {
                    $ouvrier->setEquipe($equipe);
                    $entityManager->persist($ouvrier);
                }

                // Mettre à jour la relation du chef d'équipe
                if ($equipe->getChefEquipe()) {
                    $chef = $equipe->getChefEquipe();
                    $chef->setEquipe($equipe);
                    $entityManager->persist($chef);
                }

                $entityManager->persist($equipe);
                $entityManager->flush();

                $this->addFlash('success', 'Équipe modifiée avec succès.');
                return $this->redirectToRoute('app_equipe_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $this->addFlash('error', 'Formulaire invalide: ' . $form->getErrors(true)->__toString());
            }
        }

        return $this->render('equipe/edit.html.twig', [
            'equipe' => $equipe,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_equipe_delete', methods: ['POST'])]
    public function delete(Request $request, Equipe $equipe, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$equipe->getId(), $request->request->get('_token'))) {
            $entityManager->remove($equipe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_equipe_index', [], Response::HTTP_SEE_OTHER);
    }
}
