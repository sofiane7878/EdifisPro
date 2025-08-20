<?php

namespace App\Tests\Entity;

use App\Entity\Competence;
use App\Entity\Ouvrier;
use App\Entity\Equipe;
use App\Entity\Chantier;
use PHPUnit\Framework\TestCase;

class CompetenceTest extends TestCase
{
    private Competence $competence;

    protected function setUp(): void
    {
        $this->competence = new Competence();
    }

    public function testCompetenceCreation(): void
    {
        $this->assertInstanceOf(Competence::class, $this->competence);
    }

    public function testCompetenceNom(): void
    {
        $nom = 'Maçon';
        $this->competence->setNom($nom);
        
        $this->assertEquals($nom, $this->competence->getNom());
    }

    public function testCompetenceDescription(): void
    {
        $description = 'Spécialiste de la maçonnerie';
        $this->competence->setDescription($description);
        
        $this->assertEquals($description, $this->competence->getDescription());
    }

    public function testCompetenceCategorie(): void
    {
        $categorie = 'Gros œuvre';
        $this->competence->setCategorie($categorie);
        
        $this->assertEquals($categorie, $this->competence->getCategorie());
    }

    public function testCompetenceOuvriers(): void
    {
        $ouvrier1 = new Ouvrier();
        $ouvrier1->setNomOuvrier('Dupont');
        
        $ouvrier2 = new Ouvrier();
        $ouvrier2->setNomOuvrier('Martin');

        $this->competence->addOuvrier($ouvrier1);
        $this->competence->addOuvrier($ouvrier2);

        $this->assertCount(2, $this->competence->getOuvriers());
        $this->assertTrue($this->competence->getOuvriers()->contains($ouvrier1));
        $this->assertTrue($this->competence->getOuvriers()->contains($ouvrier2));
    }

    public function testCompetenceEquipes(): void
    {
        $equipe1 = new Equipe();
        $equipe1->setNomEquipe('Équipe A');
        
        $equipe2 = new Equipe();
        $equipe2->setNomEquipe('Équipe B');

        $this->competence->addEquipe($equipe1);
        $this->competence->addEquipe($equipe2);

        $this->assertCount(2, $this->competence->getEquipes());
        $this->assertTrue($this->competence->getEquipes()->contains($equipe1));
        $this->assertTrue($this->competence->getEquipes()->contains($equipe2));
    }

    public function testCompetenceChantiers(): void
    {
        $chantier1 = new Chantier();
        $chantier1->setNom('Chantier A');
        
        $chantier2 = new Chantier();
        $chantier2->setNom('Chantier B');

        $this->competence->addChantier($chantier1);
        $this->competence->addChantier($chantier2);

        $this->assertCount(2, $this->competence->getChantiers());
        $this->assertTrue($this->competence->getChantiers()->contains($chantier1));
        $this->assertTrue($this->competence->getChantiers()->contains($chantier2));
    }

    public function testCompetenceRemoveOuvrier(): void
    {
        $ouvrier = new Ouvrier();
        $ouvrier->setNomOuvrier('Dupont');

        $this->competence->addOuvrier($ouvrier);
        $this->assertCount(1, $this->competence->getOuvriers());

        $this->competence->removeOuvrier($ouvrier);
        $this->assertCount(0, $this->competence->getOuvriers());
    }

    public function testCompetenceRemoveEquipe(): void
    {
        $equipe = new Equipe();
        $equipe->setNomEquipe('Équipe A');

        $this->competence->addEquipe($equipe);
        $this->assertCount(1, $this->competence->getEquipes());

        $this->competence->removeEquipe($equipe);
        $this->assertCount(0, $this->competence->getEquipes());
    }

    public function testCompetenceRemoveChantier(): void
    {
        $chantier = new Chantier();
        $chantier->setNom('Chantier A');

        $this->competence->addChantier($chantier);
        $this->assertCount(1, $this->competence->getChantiers());

        $this->competence->removeChantier($chantier);
        $this->assertCount(0, $this->competence->getChantiers());
    }

    public function testCompetenceToString(): void
    {
        $nom = 'Maçon';
        $this->competence->setNom($nom);
        
        $this->assertEquals($nom, (string) $this->competence);
    }
}
