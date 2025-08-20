<?php

namespace App\Tests\Entity;

use App\Entity\Ouvrier;
use App\Entity\Competence;
use App\Entity\Equipe;
use PHPUnit\Framework\TestCase;

class OuvrierTest extends TestCase
{
    private Ouvrier $ouvrier;

    protected function setUp(): void
    {
        $this->ouvrier = new Ouvrier();
    }

    public function testOuvrierCreation(): void
    {
        $this->assertInstanceOf(Ouvrier::class, $this->ouvrier);
    }

    public function testOuvrierNom(): void
    {
        $nom = 'Dupont';
        $this->ouvrier->setNomOuvrier($nom);
        
        $this->assertEquals($nom, $this->ouvrier->getNomOuvrier());
    }

    public function testOuvrierCompetences(): void
    {
        $competence1 = new Competence();
        $competence1->setNom('Maçon');
        
        $competence2 = new Competence();
        $competence2->setNom('Plâtrier');

        $this->ouvrier->addCompetence($competence1);
        $this->ouvrier->addCompetence($competence2);

        $this->assertCount(2, $this->ouvrier->getCompetences());
        $this->assertTrue($this->ouvrier->getCompetences()->contains($competence1));
        $this->assertTrue($this->ouvrier->getCompetences()->contains($competence2));
    }

    public function testOuvrierRemoveCompetence(): void
    {
        $competence = new Competence();
        $competence->setNom('Maçon');

        $this->ouvrier->addCompetence($competence);
        $this->assertCount(1, $this->ouvrier->getCompetences());

        $this->ouvrier->removeCompetence($competence);
        $this->assertCount(0, $this->ouvrier->getCompetences());
    }

    public function testOuvrierEquipe(): void
    {
        $equipe = new Equipe();
        $equipe->setNomEquipe('Équipe A');

        $this->ouvrier->setEquipe($equipe);
        
        $this->assertEquals($equipe, $this->ouvrier->getEquipe());
    }

    public function testOuvrierRole(): void
    {
        $role = 'Ouvrier';
        $this->ouvrier->setRole($role);
        
        $this->assertEquals($role, $this->ouvrier->getRole());
    }

    public function testOuvrierUser(): void
    {
        // Test que la méthode getUser existe
        $this->assertNull($this->ouvrier->getUser());
    }
}
