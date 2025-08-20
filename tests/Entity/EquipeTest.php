<?php

namespace App\Tests\Entity;

use App\Entity\Equipe;
use App\Entity\Ouvrier;
use App\Entity\Competence;
use PHPUnit\Framework\TestCase;

class EquipeTest extends TestCase
{
    private Equipe $equipe;

    protected function setUp(): void
    {
        $this->equipe = new Equipe();
    }

    public function testEquipeCreation(): void
    {
        $this->assertInstanceOf(Equipe::class, $this->equipe);
    }

    public function testEquipeNom(): void
    {
        $nom = 'Équipe Alpha';
        $this->equipe->setNomEquipe($nom);
        
        $this->assertEquals($nom, $this->equipe->getNomEquipe());
    }

    public function testEquipeNombre(): void
    {
        $nombre = 5;
        $this->equipe->setNombre($nombre);
        
        $this->assertEquals($nombre, $this->equipe->getNombre());
    }

    public function testEquipeCompetences(): void
    {
        $competence1 = new Competence();
        $competence1->setNom('Maçon');
        
        $competence2 = new Competence();
        $competence2->setNom('Plâtrier');

        $this->equipe->addCompetence($competence1);
        $this->equipe->addCompetence($competence2);

        $this->assertCount(2, $this->equipe->getCompetences());
        $this->assertTrue($this->equipe->getCompetences()->contains($competence1));
        $this->assertTrue($this->equipe->getCompetences()->contains($competence2));
    }

    public function testEquipeOuvriers(): void
    {
        $ouvrier1 = new Ouvrier();
        $ouvrier1->setNomOuvrier('Dupont');
        
        $ouvrier2 = new Ouvrier();
        $ouvrier2->setNomOuvrier('Martin');

        $this->equipe->addOuvrier($ouvrier1);
        $this->equipe->addOuvrier($ouvrier2);

        $this->assertCount(2, $this->equipe->getOuvriers());
        $this->assertEquals($this->equipe, $ouvrier1->getEquipe());
        $this->assertEquals($this->equipe, $ouvrier2->getEquipe());
    }

    public function testEquipeChefEquipe(): void
    {
        $chef = new Ouvrier();
        $chef->setNomOuvrier('Chef Équipe');
        $chef->setRole('Chef');

        $this->equipe->setChefEquipe($chef);
        
        $this->assertEquals($chef, $this->equipe->getChefEquipe());
    }

    public function testEquipeRemoveOuvrier(): void
    {
        $ouvrier = new Ouvrier();
        $ouvrier->setNomOuvrier('Dupont');

        $this->equipe->addOuvrier($ouvrier);
        $this->assertCount(1, $this->equipe->getOuvriers());

        $this->equipe->removeOuvrier($ouvrier);
        $this->assertCount(0, $this->equipe->getOuvriers());
        $this->assertNull($ouvrier->getEquipe());
    }

    public function testEquipeRemoveCompetence(): void
    {
        $competence = new Competence();
        $competence->setNom('Maçon');

        $this->equipe->addCompetence($competence);
        $this->assertCount(1, $this->equipe->getCompetences());

        $this->equipe->removeCompetence($competence);
        $this->assertCount(0, $this->equipe->getCompetences());
    }

    public function testEquipeCompetanceEquipe(): void
    {
        $competence1 = new Competence();
        $competence1->setNom('Maçon');
        
        $competence2 = new Competence();
        $competence2->setNom('Plâtrier');

        $this->equipe->addCompetence($competence1);
        $this->equipe->addCompetence($competence2);

        $competences = $this->equipe->getCompetanceEquipe();
        
        $this->assertIsArray($competences);
        $this->assertContains('Maçon', $competences);
        $this->assertContains('Plâtrier', $competences);
    }
}
