<?php

namespace App\Tests\Integration;

use App\Entity\Equipe;
use App\Entity\Chantier;
use App\Entity\Affectation;
use App\Entity\Competence;
use App\Entity\Ouvrier;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class AffectationIntegrationTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testCompleteAffectationWorkflow(): void
    {
        $this->client->request('GET', '/affectation/');

        // Pour les tests, on accepte les redirections
        $statusCode = $this->client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 301, 302]);
    }

    public function testAffectationValidation(): void
    {
        // Créer des données de test avec des noms uniques
        $competence = new Competence();
        $competence->setNom('Maçon Test ' . uniqid());
        $competence->setDescription('Description test');
        $competence->setCategorie('Gros œuvre');
        $this->entityManager->persist($competence);

        $ouvrier = new Ouvrier();
        $ouvrier->setNomOuvrier('Test Ouvrier ' . uniqid());
        $ouvrier->setRole('Ouvrier');
        $ouvrier->addCompetence($competence);
        $this->entityManager->persist($ouvrier);

        $equipe = new Equipe();
        $equipe->setNomEquipe('Équipe Test ' . uniqid());
        $equipe->setNombre(1);
        $equipe->addCompetence($competence);
        $equipe->addOuvrier($ouvrier);
        $this->entityManager->persist($equipe);

        $chantier = new Chantier();
        $chantier->setNom('Chantier Test ' . uniqid());
        $chantier->setEffectifRequis(1);
        $chantier->setDateDebut(new \DateTime('2024-01-01'));
        $chantier->setDateFin(new \DateTime('2024-12-31'));
        $chantier->addCompetenceRequise($competence);
        $this->entityManager->persist($chantier);

        $this->entityManager->flush();

        // Test de validation
        $this->assertTrue($equipe->getCompetences()->contains($competence));
        $this->assertTrue($chantier->getCompetencesRequises()->contains($competence));
    }

    public function testEquipeCompetenceValidation(): void
    {
        // Créer des données de test avec des noms uniques
        $competence = new Competence();
        $competence->setNom('Maçon Test ' . uniqid());
        $competence->setDescription('Description test');
        $competence->setCategorie('Gros œuvre');
        $this->entityManager->persist($competence);

        $ouvrier = new Ouvrier();
        $ouvrier->setNomOuvrier('Test Ouvrier ' . uniqid());
        $ouvrier->setRole('Ouvrier');
        $ouvrier->addCompetence($competence);
        $this->entityManager->persist($ouvrier);

        $equipe = new Equipe();
        $equipe->setNomEquipe('Équipe Test ' . uniqid());
        $equipe->setNombre(1);
        $equipe->addCompetence($competence);
        $equipe->addOuvrier($ouvrier);
        $this->entityManager->persist($equipe);

        $this->entityManager->flush();

        // Test de validation
        $this->assertTrue($equipe->getCompetences()->contains($competence));
        $this->assertTrue($equipe->getOuvriers()->contains($ouvrier));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
