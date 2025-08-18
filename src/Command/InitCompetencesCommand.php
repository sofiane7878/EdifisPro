<?php

namespace App\Command;

use App\Entity\Competence;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:init-competences',
    description: 'Initialise les compétences de base dans la base de données',
)]
class InitCompetencesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $competences = [
            [
                'nom' => 'Maçon',
                'categorie' => 'Gros œuvre',
                'description' => 'Spécialiste de la maçonnerie, pose de briques, blocs et pierres'
            ],
            [
                'nom' => 'Coffreur',
                'categorie' => 'Gros œuvre',
                'description' => 'Spécialiste du coffrage pour béton armé'
            ],
            [
                'nom' => 'Ferrailleur',
                'categorie' => 'Gros œuvre',
                'description' => 'Pose et assemblage des armatures métalliques'
            ],
            [
                'nom' => 'Terrassier',
                'categorie' => 'Gros œuvre',
                'description' => 'Préparation du terrain et excavation'
            ],
            [
                'nom' => 'Conducteur d\'engins',
                'categorie' => 'Technique',
                'description' => 'Conduite d\'engins de chantier (pelleteuse, bulldozer, etc.)'
            ],
            [
                'nom' => 'Manœuvre de chantier',
                'categorie' => 'Gros œuvre',
                'description' => 'Tâches générales de manutention et d\'assistance'
            ],
            [
                'nom' => 'Grutier',
                'categorie' => 'Technique',
                'description' => 'Conduite de grues et manutention de charges lourdes'
            ],
            [
                'nom' => 'Plombier',
                'categorie' => 'Second œuvre',
                'description' => 'Installation et réparation des systèmes de plomberie'
            ],
            [
                'nom' => 'Électricien',
                'categorie' => 'Second œuvre',
                'description' => 'Installation et maintenance des systèmes électriques'
            ],
            [
                'nom' => 'Peintre en bâtiment',
                'categorie' => 'Finitions',
                'description' => 'Application de peintures et revêtements'
            ],
            [
                'nom' => 'Plâtrier',
                'categorie' => 'Second œuvre',
                'description' => 'Pose de cloisons et enduits au plâtre'
            ],
            [
                'nom' => 'Carreleur',
                'categorie' => 'Finitions',
                'description' => 'Pose de carrelages et revêtements de sols'
            ],
            [
                'nom' => 'Menuisier',
                'categorie' => 'Second œuvre',
                'description' => 'Fabrication et pose d\'éléments en bois'
            ],
            [
                'nom' => 'Parqueteur',
                'categorie' => 'Finitions',
                'description' => 'Pose de parquets et revêtements de sols en bois'
            ],
            [
                'nom' => 'Serrurier-métallier',
                'categorie' => 'Second œuvre',
                'description' => 'Travaux de serrurerie et métallerie'
            ],
            [
                'nom' => 'Chauffagiste',
                'categorie' => 'Second œuvre',
                'description' => 'Installation et maintenance des systèmes de chauffage'
            ],
            [
                'nom' => 'Enduiseur',
                'categorie' => 'Finitions',
                'description' => 'Application d\'enduits et de mortiers de finition'
            ],
            [
                'nom' => 'Vitrificateur',
                'categorie' => 'Finitions',
                'description' => 'Application de vernis et finitions protectrices'
            ],
            [
                'nom' => 'Solier-moquettiste',
                'categorie' => 'Finitions',
                'description' => 'Pose de moquettes et revêtements de sols souples'
            ],
            [
                'nom' => 'Staffeur-Ornemaniste',
                'categorie' => 'Spécialisée',
                'description' => 'Création d\'ornements en plâtre et décors architecturaux'
            ],
        ];

        $io->title('Initialisation des compétences de base');

        $count = 0;
        foreach ($competences as $competenceData) {
            // Vérifier si la compétence existe déjà
            $existing = $this->entityManager->getRepository(Competence::class)
                ->findOneBy(['nom' => $competenceData['nom']]);

            if (!$existing) {
                $competence = new Competence();
                $competence->setNom($competenceData['nom']);
                $competence->setCategorie($competenceData['categorie']);
                $competence->setDescription($competenceData['description']);

                $this->entityManager->persist($competence);
                $count++;

                $io->text(sprintf('✓ Créée : %s', $competenceData['nom']));
            } else {
                $io->text(sprintf('⚠ Existe déjà : %s', $competenceData['nom']));
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf('%d compétences ont été créées avec succès !', $count));

        return Command::SUCCESS;
    }
} 