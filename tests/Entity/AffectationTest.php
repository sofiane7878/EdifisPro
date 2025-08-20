<?php

namespace App\Tests\Entity;

use App\Entity\Affectation;
use App\Entity\Equipe;
use App\Entity\Chantier;
use PHPUnit\Framework\TestCase;

class AffectationTest extends TestCase
{
    private Affectation $affectation;

    protected function setUp(): void
    {
        $this->affectation = new Affectation();
    }

    public function testAffectationCreation(): void
    {
        $this->assertInstanceOf(Affectation::class, $this->affectation);
    }

    public function testAffectationNom(): void
    {
        $nom = 'Affectation Test';
        $this->affectation->setNom($nom);
        
        $this->assertEquals($nom, $this->affectation->getNom());
    }

    public function testAffectationDates(): void
    {
        $dateDebut = new \DateTime('2024-01-01');
        $dateFin = new \DateTime('2024-12-31');

        $this->affectation->setDateDebut($dateDebut);
        $this->affectation->setDateFin($dateFin);

        $this->assertEquals($dateDebut, $this->affectation->getDateDebut());
        $this->assertEquals($dateFin, $this->affectation->getDateFin());
    }

    public function testAffectationEquipe(): void
    {
        $equipe = new Equipe();
        $equipe->setNomEquipe('Équipe Test');

        $this->affectation->setEquipe($equipe);
        
        $this->assertEquals($equipe, $this->affectation->getEquipe());
    }

    public function testAffectationChantier(): void
    {
        $chantier = new Chantier();
        $chantier->setNom('Chantier Test');

        $this->affectation->setChantier($chantier);
        
        $this->assertEquals($chantier, $this->affectation->getChantier());
    }

    public function testAffectationCompleteWorkflow(): void
    {
        // Créer une équipe
        $equipe = new Equipe();
        $equipe->setNomEquipe('Équipe Test');
        $equipe->setNombre(3);

        // Créer un chantier
        $chantier = new Chantier();
        $chantier->setNom('Chantier Test');
        $chantier->setEffectifRequis(3);

        // Configurer l'affectation
        $this->affectation->setNom('Affectation Complète');
        $this->affectation->setDateDebut(new \DateTime('2024-01-01'));
        $this->affectation->setDateFin(new \DateTime('2024-12-31'));
        $this->affectation->setEquipe($equipe);
        $this->affectation->setChantier($chantier);

        // Vérifications
        $this->assertEquals('Affectation Complète', $this->affectation->getNom());
        $this->assertEquals($equipe, $this->affectation->getEquipe());
        $this->assertEquals($chantier, $this->affectation->getChantier());
        $this->assertEquals(new \DateTime('2024-01-01'), $this->affectation->getDateDebut());
        $this->assertEquals(new \DateTime('2024-12-31'), $this->affectation->getDateFin());
    }

    public function testAffectationDateValidation(): void
    {
        $dateDebut = new \DateTime('2024-01-01');
        $dateFin = new \DateTime('2024-12-31');

        $this->affectation->setDateDebut($dateDebut);
        $this->affectation->setDateFin($dateFin);

        // Vérifier que la date de fin est après la date de début
        $this->assertGreaterThan($dateDebut, $dateFin);
    }
}
