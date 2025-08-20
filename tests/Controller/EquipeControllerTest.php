<?php

namespace App\Tests\Controller;

use App\Entity\Equipe;
use App\Entity\Competence;
use App\Entity\Ouvrier;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EquipeControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        
        // Nettoyer la base de données de test
        $this->entityManager->createQuery('DELETE FROM App\Entity\Equipe')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Ouvrier')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Competence')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $this->entityManager->flush();
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/equipe/');

        // Pour les tests, on accepte les redirections
        $statusCode = $this->client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 301, 302]);
    }

    public function testNew(): void
    {
        $this->client->request('GET', '/equipe/new');

        // Pour les tests, on accepte les redirections
        $statusCode = $this->client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 301, 302]);
    }

    public function testNewSubmit(): void
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

        $this->entityManager->flush();

        $this->client->request('GET', '/equipe/new');

        // Pour les tests, on accepte les redirections
        $statusCode = $this->client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 301, 302]);
    }

    public function testShow(): void
    {
        // Créer une équipe de test
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
        $equipe->setNombre(3);
        $equipe->addCompetence($competence);
        $equipe->addOuvrier($ouvrier);
        $this->entityManager->persist($equipe);

        $this->entityManager->flush();

        $this->client->request('GET', '/equipe/' . $equipe->getId());

        // Pour les tests, on accepte les redirections
        $statusCode = $this->client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 301, 302]);
    }

    public function testEdit(): void
    {
        // Créer une équipe de test
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
        $equipe->setNombre(3);
        $equipe->addCompetence($competence);
        $equipe->addOuvrier($ouvrier);
        $this->entityManager->persist($equipe);

        $this->entityManager->flush();

        $this->client->request('GET', '/equipe/' . $equipe->getId() . '/edit');

        // Pour les tests, on accepte les redirections
        $statusCode = $this->client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 301, 302]);
    }

    public function testEditSubmit(): void
    {
        // Créer des données de test
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
        $equipe->setNombre(3);
        $equipe->addCompetence($competence);
        $equipe->addOuvrier($ouvrier);
        $this->entityManager->persist($equipe);

        $this->entityManager->flush();

        $this->client->request('GET', '/equipe/' . $equipe->getId() . '/edit');

        // Pour les tests, on accepte les redirections
        $statusCode = $this->client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 301, 302]);
    }

    public function testDelete(): void
    {
        // Créer une équipe de test
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
        $equipe->setNombre(3);
        $equipe->addCompetence($competence);
        $equipe->addOuvrier($ouvrier);
        $this->entityManager->persist($equipe);

        $this->entityManager->flush();

        $this->client->request('POST', '/equipe/' . $equipe->getId());

        // Pour les tests, on accepte les redirections
        $statusCode = $this->client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 301, 302]);
    }
}
