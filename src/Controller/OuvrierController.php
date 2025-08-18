<?php

namespace App\Controller;

use App\Entity\Ouvrier;
use App\Entity\User;
use App\Form\OuvrierType;
use App\Repository\OuvrierRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/ouvrier')]
#[IsGranted('ROLE_ADMIN')]

final class OuvrierController extends AbstractController
{
    #[Route('/', name: 'app_ouvrier_index', methods: ['GET'])]
    public function index(OuvrierRepository $ouvrierRepository): Response
    {
        return $this->render('ouvrier/index.html.twig', [
            'ouvriers' => $ouvrierRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_ouvrier_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository
    ): Response {
        $ouvrier = new Ouvrier();
        $form = $this->createForm(OuvrierType::class, $ouvrier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ouvrier);
            $entityManager->flush();

            // Créer le compte utilisateur si demandé
            $createUserAccount = $form->get('createUserAccount')->getData();
            if ($createUserAccount) {
                $prenom = $form->get('prenom')->getData();
                $email = $form->get('email')->getData();
                
                if ($prenom && $email) {
                    $this->createUserAccount($ouvrier, $prenom, $email, $passwordHasher, $entityManager, $userRepository);
                }
            }

            $this->addFlash('success', 'Ouvrier créé avec succès' . ($createUserAccount ? ' et compte utilisateur généré' : ''));
            return $this->redirectToRoute('app_ouvrier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ouvrier/new.html.twig', [
            'ouvrier' => $ouvrier,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_ouvrier_show', methods: ['GET'])]
    public function show(int $id, OuvrierRepository $ouvrierRepository): Response
    {
        $ouvrier = $ouvrierRepository->find($id);

        if (!$ouvrier) {
            throw new NotFoundHttpException('Ouvrier non trouvé');
        }

        return $this->render('ouvrier/show.html.twig', [
            'ouvrier' => $ouvrier,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ouvrier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, OuvrierRepository $ouvrierRepository, EntityManagerInterface $entityManager): Response
    {
        $ouvrier = $ouvrierRepository->find($id);

        if (!$ouvrier) {
            throw new NotFoundHttpException('Ouvrier non trouvé');
        }

        $form = $this->createForm(OuvrierType::class, $ouvrier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_ouvrier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ouvrier/edit.html.twig', [
            'ouvrier' => $ouvrier,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_ouvrier_delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, Ouvrier $ouvrier, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $ouvrier->getId(), $request->request->get('_token'))) {
            $entityManager->remove($ouvrier);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_ouvrier_index');
    }

    /**
     * Crée un compte utilisateur pour un ouvrier
     */
    private function createUserAccount(
        Ouvrier $ouvrier, 
        string $prenom, 
        string $email, 
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): void {
        // Vérifier si l'email existe déjà
        $existingUser = $userRepository->findOneBy(['email' => $email]);
        if ($existingUser) {
            // Générer un email alternatif
            $email = $this->generateAlternativeEmail($ouvrier->getNomOuvrier(), $prenom, $userRepository);
        }

        // Créer le compte utilisateur
        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_OUVRIER']);
        
        // Hasher le mot de passe par défaut
        $hashedPassword = $passwordHasher->hashPassword($user, 'ouvrier123');
        $user->setPassword($hashedPassword);
        
        // Lier l'utilisateur à l'ouvrier
        $user->setOuvrier($ouvrier);
        $ouvrier->setUser($user);

        $entityManager->persist($user);
        $entityManager->flush();
    }

    /**
     * Génère un email alternatif si l'email existe déjà
     */
    private function generateAlternativeEmail(string $nom, string $prenom, UserRepository $userRepository): string
    {
        $baseEmail = $this->generateEmail($nom, $prenom);
        $counter = 1;
        $email = $baseEmail;

        while ($userRepository->findOneBy(['email' => $email])) {
            $email = str_replace('@btp.com', $counter . '@btp.com', $baseEmail);
            $counter++;
        }

        return $email;
    }

    /**
     * Génère l'email à partir du nom et prénom
     */
    private function generateEmail(string $nom, string $prenom): string
    {
        // Nettoyer les caractères spéciaux et accents
        $nom = $this->normalizeString($nom);
        $prenom = $this->normalizeString($prenom);
        
        return strtolower($nom . '.' . $prenom . '@btp.com');
    }

    /**
     * Normalise une chaîne de caractères (supprime accents et caractères spéciaux)
     */
    private function normalizeString(string $string): string
    {
        // Remplacer les caractères accentués
        $string = str_replace(
            ['à', 'á', 'â', 'ã', 'ä', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý'],
            ['a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y'],
            $string
        );
        
        // Supprimer les espaces et caractères spéciaux
        $string = preg_replace('/[^a-zA-Z0-9]/', '', $string);
        
        return $string;
    }
}
