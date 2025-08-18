<?php

namespace App\Command;

use App\Entity\Ouvrier;
use App\Entity\User;
use App\Entity\Competence;
use App\Repository\CompetenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:generate-test-data',
    description: 'Génère un jeu de données de test avec des ouvriers, compétences et comptes utilisateurs',
)]
class GenerateTestDataCommand extends Command
{
    private array $nomsOuvriers = [
        'Jean Dupont', 'Marie Martin', 'Pierre Durand', 'Sophie Bernard', 'Michel Petit',
        'Isabelle Moreau', 'François Leroy', 'Nathalie Simon', 'Philippe Michel', 'Catherine Roux',
        'André David', 'Monique Bertrand', 'Robert Rivière', 'Jacqueline Brun', 'Marcel Meunier',
        'Françoise Blanchard', 'Henri Guerin', 'Suzanne Bonnet', 'Georges Lemoine', 'Thérèse Caron',
        'Marcel Clement', 'Jeanne Denis', 'Roger Leroux', 'Madeleine Mercier', 'Claude Faure',
        'Nicole Andre', 'Guy Gauthier', 'Danielle Joly', 'Jean-Pierre Chauvin', 'Monique Menard',
        'Michel Leblanc', 'Françoise Girard', 'Pierre Bonnet', 'Marie Lemoine', 'Jacques Brun',
        'Suzanne Caron', 'André Clement', 'Thérèse Denis', 'Marcel Leroux', 'Jeanne Mercier',
        'Roger Faure', 'Madeleine Andre', 'Claude Gauthier', 'Nicole Joly', 'Guy Chauvin',
        'Danielle Menard', 'Jean-Pierre Leblanc', 'Monique Girard', 'Michel Bonnet', 'Françoise Lemoine'
    ];

    private array $rolesOuvriers = [
        'Ouvrier', 'Chef'
    ];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('count', null, InputOption::VALUE_OPTIONAL, 'Nombre d\'ouvriers à créer', 50)
            ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'Mot de passe par défaut pour tous les comptes', 'ouvrier123')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $count = (int) $input->getOption('count');
        $defaultPassword = $input->getOption('password');

        $io->title('Génération du jeu de données de test');
        $io->text(sprintf('Création de %d ouvriers avec leurs comptes utilisateurs...', $count));

        // Récupérer toutes les compétences disponibles
        $competences = $this->entityManager->getRepository(Competence::class)->findAll();
        
        if (empty($competences)) {
            $io->error('Aucune compétence trouvée. Veuillez d\'abord exécuter app:init-competences');
            return Command::FAILURE;
        }

        $io->info(sprintf('Compétences disponibles: %d', count($competences)));

        $progressBar = $io->createProgressBar($count);
        $progressBar->start();

        $createdUsers = [];
        $createdOuvriers = [];

        for ($i = 0; $i < $count; $i++) {
            // Créer l'ouvrier
            $ouvrier = new Ouvrier();
            $nomOuvrier = $this->nomsOuvriers[$i % count($this->nomsOuvriers)];
            $suffix = $i > count($this->nomsOuvriers) - 1 ? ' ' . ($i + 1) : '';
            $ouvrier->setNomOuvrier($nomOuvrier . $suffix);
            $ouvrier->setRole($this->rolesOuvriers[array_rand($this->rolesOuvriers)]);

            // Attribuer des compétences aléatoires (1 à 4 compétences par ouvrier)
            $nbCompetences = rand(1, 4);
            $competencesOuvrier = array_rand($competences, $nbCompetences);
            if (!is_array($competencesOuvrier)) {
                $competencesOuvrier = [$competencesOuvrier];
            }
            
            foreach ($competencesOuvrier as $index) {
                $ouvrier->addCompetence($competences[$index]);
            }

            $this->entityManager->persist($ouvrier);

            // Créer le compte utilisateur
            $user = new User();
            $email = $this->generateEmail($nomOuvrier, $i);
            $user->setEmail($email);
            $user->setRoles(['ROLE_OUVRIER']);
            
            // Hasher le mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($user, $defaultPassword);
            $user->setPassword($hashedPassword);
            
            // Associer l'ouvrier au compte
            $user->setOuvrier($ouvrier);

            $this->entityManager->persist($user);

            $createdUsers[] = [
                'email' => $email,
                'password' => $defaultPassword,
                'nom' => $ouvrier->getNomOuvrier(),
                'role' => $ouvrier->getRole(),
                'competences' => $ouvrier->getCompetences()->count()
            ];

            $createdOuvriers[] = $ouvrier;

            $progressBar->advance();

            // Flush tous les 10 éléments pour éviter les problèmes de mémoire
            if (($i + 1) % 10 === 0) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();
        $progressBar->finish();
        $io->newLine(2);

        // Afficher un résumé
        $io->success(sprintf('%d ouvriers créés avec succès !', $count));

        // Afficher quelques exemples de comptes créés
        $io->section('Exemples de comptes créés:');
        $io->table(
            ['Email', 'Mot de passe', 'Nom', 'Rôle', 'Compétences'],
            array_slice($createdUsers, 0, 10)
        );

        if (count($createdUsers) > 10) {
            $io->note(sprintf('... et %d autres comptes', count($createdUsers) - 10));
        }

        // Sauvegarder la liste complète dans un fichier
        $this->saveAccountsToFile($createdUsers, $io);

        $io->success('Génération terminée !');
        $io->info('Tous les ouvriers ont le rôle ROLE_OUVRIER et peuvent se connecter à l\'application.');

        return Command::SUCCESS;
    }

    private function generateEmail(string $nom, int $index): string
    {
        $nomNormalise = $this->normalizeString($nom);
        $prenom = explode(' ', $nom)[0];
        $nomFamille = explode(' ', $nom)[1] ?? 'ouvrier';
        
        $prenomNormalise = $this->normalizeString($prenom);
        $nomFamilleNormalise = $this->normalizeString($nomFamille);
        
        return sprintf('%s.%s%d@btp.com', $prenomNormalise, $nomFamilleNormalise, $index + 1);
    }

    private function normalizeString(string $str): string
    {
        $str = strtolower($str);
        $str = str_replace(
            ['à', 'á', 'â', 'ã', 'ä', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ'],
            ['a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y'],
            $str
        );
        $str = preg_replace('/[^a-z0-9]/', '', $str);
        return $str;
    }

    private function saveAccountsToFile(array $accounts, SymfonyStyle $io): void
    {
        $filename = 'comptes_ouvriers_' . date('Y-m-d_H-i-s') . '.txt';
        $content = "Comptes ouvriers générés le " . date('d/m/Y H:i:s') . "\n";
        $content .= "==========================================\n\n";
        
        foreach ($accounts as $account) {
            $content .= sprintf(
                "Email: %s\nMot de passe: %s\nNom: %s\nRôle: %s\nCompétences: %d\n",
                $account['email'],
                $account['password'],
                $account['nom'],
                $account['role'],
                $account['competences']
            );
            $content .= "------------------------------------------\n";
        }

        file_put_contents($filename, $content);
        $io->info(sprintf('Liste complète des comptes sauvegardée dans: %s', $filename));
    }
} 