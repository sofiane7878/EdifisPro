<?php

namespace App\Tests\DataFixtures;

use App\Entity\Competence;
use App\Entity\Ouvrier;
use App\Entity\Equipe;
use App\Entity\Chantier;
use App\Entity\Affectation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TestFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Créer des compétences
        $competences = ['Maçon', 'Plâtrier', 'Électricien', 'Plombier', 'Menuisier'];
        $competenceEntities = [];

        foreach ($competences as $nom) {
            $competence = new Competence();
            $competence->setNom($nom);
            $competence->setDescription($faker->sentence());
            $competence->setCategorie('Gros œuvre');
            $manager->persist($competence);
            $competenceEntities[] = $competence;
        }

        // Créer des ouvriers
        for ($i = 0; $i < 10; $i++) {
            $ouvrier = new Ouvrier();
            $ouvrier->setNomOuvrier($faker->lastName());
            $ouvrier->setRole('Ouvrier');
            $ouvrier->setEmail($faker->email());
            
            // Ajouter 1-3 compétences aléatoires
            $nbCompetences = rand(1, 3);
            $competencesOuvrier = array_rand($competenceEntities, $nbCompetences);
            if (!is_array($competencesOuvrier)) {
                $competencesOuvrier = [$competencesOuvrier];
            }
            
            foreach ($competencesOuvrier as $index) {
                $ouvrier->addCompetence($competenceEntities[$index]);
            }
            
            $manager->persist($ouvrier);
        }

        // Créer des équipes
        for ($i = 0; $i < 3; $i++) {
            $equipe = new Equipe();
            $equipe->setNomEquipe('Équipe ' . ($i + 1));
            $equipe->setNombre(rand(3, 6));
            
            // Ajouter 2-4 compétences aléatoires
            $nbCompetences = rand(2, 4);
            $competencesEquipe = array_rand($competenceEntities, $nbCompetences);
            if (!is_array($competencesEquipe)) {
                $competencesEquipe = [$competencesEquipe];
            }
            
            foreach ($competencesEquipe as $index) {
                $equipe->addCompetence($competenceEntities[$index]);
            }
            
            $manager->persist($equipe);
        }

        // Créer des chantiers
        for ($i = 0; $i < 5; $i++) {
            $chantier = new Chantier();
            $chantier->setNom('Chantier ' . ($i + 1));
            $chantier->setEffectifRequis(rand(3, 8));
            $chantier->setDateDebut($faker->dateTimeBetween('now', '+1 month'));
            $chantier->setDateFin($faker->dateTimeBetween('+2 months', '+6 months'));
            
            // Ajouter 1-3 compétences requises aléatoires
            $nbCompetences = rand(1, 3);
            $competencesChantier = array_rand($competenceEntities, $nbCompetences);
            if (!is_array($competencesChantier)) {
                $competencesChantier = [$competencesChantier];
            }
            
            foreach ($competencesChantier as $index) {
                $chantier->addCompetenceRequise($competenceEntities[$index]);
            }
            
            $manager->persist($chantier);
        }

        $manager->flush();
    }
}
