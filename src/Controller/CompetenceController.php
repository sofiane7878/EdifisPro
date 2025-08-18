<?php

namespace App\Controller;

use App\Entity\Competence;
use App\Form\CompetenceType;
use App\Repository\CompetenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/competence')]
#[IsGranted('ROLE_ADMIN')]
final class CompetenceController extends AbstractController
{
    #[Route(name: 'app_competence_index', methods: ['GET'])]
    public function index(CompetenceRepository $competenceRepository): Response
    {
        return $this->render('competence/index.html.twig', [
            'competences' => $competenceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_competence_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $competence = new Competence();
        $form = $this->createForm(CompetenceType::class, $competence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($competence);
            $entityManager->flush();

            $this->addFlash('success', 'Compétence créée avec succès.');
            return $this->redirectToRoute('app_competence_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('competence/new.html.twig', [
            'competence' => $competence,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_competence_show', methods: ['GET'])]
    public function show(Competence $competence): Response
    {
        return $this->render('competence/show.html.twig', [
            'competence' => $competence,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_competence_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Competence $competence, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CompetenceType::class, $competence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Compétence modifiée avec succès.');
            return $this->redirectToRoute('app_competence_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('competence/edit.html.twig', [
            'competence' => $competence,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_competence_delete', methods: ['POST'])]
    public function delete(Request $request, Competence $competence, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$competence->getId(), $request->request->get('_token'))) {
            // Vérifier si la compétence est utilisée
            if ($competence->getOuvriers()->count() > 0 || 
                $competence->getEquipes()->count() > 0 || 
                $competence->getChantiers()->count() > 0) {
                $this->addFlash('error', 'Impossible de supprimer cette compétence car elle est utilisée.');
            } else {
                $entityManager->remove($competence);
                $entityManager->flush();
                $this->addFlash('success', 'Compétence supprimée avec succès.');
            }
        }

        return $this->redirectToRoute('app_competence_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/utilisees', name: 'app_competence_utilisees', methods: ['GET'])]
    public function utilisees(CompetenceRepository $competenceRepository): Response
    {
        return $this->render('competence/utilisees.html.twig', [
            'competences' => $competenceRepository->findUtilisees(),
        ]);
    }

    #[Route('/non-utilisees', name: 'app_competence_non_utilisees', methods: ['GET'])]
    public function nonUtilisees(CompetenceRepository $competenceRepository): Response
    {
        return $this->render('competence/non_utilisees.html.twig', [
            'competences' => $competenceRepository->findNonUtilisees(),
        ]);
    }
} 