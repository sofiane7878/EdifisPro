<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Ouvrier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Crée un nouvel utilisateur avec un rôle spécifique',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Email de l\'utilisateur')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Mot de passe')
            ->addOption('role', null, InputOption::VALUE_REQUIRED, 'Rôle (ROLE_ADMIN ou ROLE_OUVRIER)')
            ->addOption('ouvrier-id', null, InputOption::VALUE_OPTIONAL, 'ID de l\'ouvrier à associer (pour ROLE_OUVRIER)')
            ->addOption('nom-ouvrier', null, InputOption::VALUE_OPTIONAL, 'Nom de l\'ouvrier à créer (pour ROLE_OUVRIER)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getOption('email');
        $password = $input->getOption('password');
        $role = $input->getOption('role');
        $ouvrierId = $input->getOption('ouvrier-id');
        $nomOuvrier = $input->getOption('nom-ouvrier');

        if (!$email || !$password || !$role) {
            $io->error('Email, password et role sont requis.');
            return Command::FAILURE;
        }

        if (!in_array($role, ['ROLE_ADMIN', 'ROLE_OUVRIER'])) {
            $io->error('Le rôle doit être ROLE_ADMIN ou ROLE_OUVRIER.');
            return Command::FAILURE;
        }

        // Vérifier si l'utilisateur existe déjà
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->error('Un utilisateur avec cet email existe déjà.');
            return Command::FAILURE;
        }

        // Créer l'utilisateur
        $user = new User();
        $user->setEmail($email);
        $user->setRoles([$role]);

        // Hasher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Si c'est un ouvrier, associer ou créer un ouvrier
        if ($role === 'ROLE_OUVRIER') {
            $ouvrier = null;

            if ($ouvrierId) {
                $ouvrier = $this->entityManager->getRepository(Ouvrier::class)->find($ouvrierId);
                if (!$ouvrier) {
                    $io->error('Ouvrier avec l\'ID ' . $ouvrierId . ' non trouvé.');
                    return Command::FAILURE;
                }
            } elseif ($nomOuvrier) {
                // Créer un nouvel ouvrier
                $ouvrier = new Ouvrier();
                $ouvrier->setNomOuvrier($nomOuvrier);
                $ouvrier->setRole('Ouvrier');
                $this->entityManager->persist($ouvrier);
            } else {
                $io->error('Pour ROLE_OUVRIER, vous devez spécifier --ouvrier-id ou --nom-ouvrier.');
                return Command::FAILURE;
            }

            if ($ouvrier) {
                $user->setOuvrier($ouvrier);
            }
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf(
            'Utilisateur créé avec succès ! Email: %s, Rôle: %s',
            $email,
            $role
        ));

        if ($role === 'ROLE_OUVRIER' && $user->getOuvrier()) {
            $io->info(sprintf('Associé à l\'ouvrier: %s', $user->getOuvrier()->getNomOuvrier()));
        }

        return Command::SUCCESS;
    }
} 