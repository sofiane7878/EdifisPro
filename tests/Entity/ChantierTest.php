<?php

namespace App\Tests\Entity;

use App\Entity\Chantier;
use App\Entity\Competence;
use App\Entity\Ouvrier;
use PHPUnit\Framework\TestCase;

class ChantierTest extends TestCase
{
    private Chantier $chantier;

    protected function setUp(): void
    {
        $this->chantier = new Chantier();
    }

    public function testChantierCreation(): void
    {
        $this->assertInstanceOf(Chantier::class, $this->chantier);
    }

    public function testChantierNom(): void
    {
        $nom = 'Construction Immeuble';
        $this->chantier->setNom($nom);
        
        $this->assertEquals($nom, $this->chantier->getNom());
    }

    public function testChantierEffectifRequis(): void
    {
        $effectif = 10;
        $this->chantier->setEffectifRequis($effectif);
        
        $this->assertEquals($effectif, $this->chantier->getEffectifRequis());
    }

    public function testChantierDates(): void
    {
        $dateDebut = new \DateTime('2024-01-01');
        $dateFin = new \DateTime('2024-12-31');

        $this->chantier->setDateDebut($dateDebut);
        $this->chantier->setDateFin($dateFin);

        $this->assertEquals($dateDebut, $this->chantier->getDateDebut());
        $this->assertEquals($dateFin, $this->chantier->getDateFin());
    }

    public function testChantierCompetencesRequises(): void
    {
        $competence1 = new Competence();
        $competence1->setNom('Maçon');
        
        $competence2 = new Competence();
        $competence2->setNom('Électricien');

        $this->chantier->addCompetenceRequise($competence1);
        $this->chantier->addCompetenceRequise($competence2);

        $this->assertCount(2, $this->chantier->getCompetencesRequises());
        $this->assertTrue($this->chantier->getCompetencesRequises()->contains($competence1));
        $this->assertTrue($this->chantier->getCompetencesRequises()->contains($competence2));
    }

    public function testChantierChefChantier(): void
    {
        $chef = new Ouvrier();
        $chef->setNomOuvrier('Chef Chantier');
        $chef->setRole('Chef');

        $this->chantier->setChefChantier($chef);
        
        $this->assertEquals($chef, $this->chantier->getChefChantier());
    }

    public function testChantierRemoveCompetenceRequise(): void
    {
        $competence = new Competence();
        $competence->setNom('Maçon');

        $this->chantier->addCompetenceRequise($competence);
        $this->assertCount(1, $this->chantier->getCompetencesRequises());

        $this->chantier->removeCompetenceRequise($competence);
        $this->assertCount(0, $this->chantier->getCompetencesRequises());
    }

    public function testChantierChantierPrerequis(): void
    {
        $competence1 = new Competence();
        $competence1->setNom('Maçon');
        
        $competence2 = new Competence();
        $competence2->setNom('Électricien');

        $this->chantier->addCompetenceRequise($competence1);
        $this->chantier->addCompetenceRequise($competence2);

        $prerequis = $this->chantier->getChantierPrerequis();
        
        $this->assertIsArray($prerequis);
        $this->assertContains('Maçon', $prerequis);
        $this->assertContains('Électricien', $prerequis);
    }

    public function testChantierImage(): void
    {
        $image = 'chantier.jpg';
        $this->chantier->setImage($image);
        
        $this->assertEquals($image, $this->chantier->getImage());
    }
}
