<?php

namespace App\Entity;

use App\Repository\EquipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipeRepository::class)]
class Equipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_equipe = null;

    #[ORM\ManyToMany(targetEntity: Competence::class, inversedBy: 'equipes')]
    #[ORM\JoinTable(name: 'equipe_competence')]
    private Collection $competences;

    #[ORM\Column]
    private ?int $nombre = null;

    #[ORM\ManyToOne(targetEntity: Ouvrier::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Ouvrier $chef_equipe = null;

    #[ORM\OneToMany(targetEntity: Ouvrier::class, mappedBy: 'equipe')]
    private Collection $ouvriers;

    #[ORM\OneToMany(targetEntity: Affectation::class, mappedBy: 'equipe')]
    private Collection $affectations;

    public function __construct()
    {
        $this->ouvriers = new ArrayCollection();
        $this->affectations = new ArrayCollection();
        $this->competences = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getNomEquipe(): ?string { return $this->nom_equipe; }
    public function setNomEquipe(string $nom_equipe): static { $this->nom_equipe = $nom_equipe; return $this; }

    /**
     * @return Collection<int, Competence>
     */
    public function getCompetences(): Collection
    {
        return $this->competences;
    }

    public function addCompetence(Competence $competence): static
    {
        if (!$this->competences->contains($competence)) {
            $this->competences->add($competence);
        }
        return $this;
    }

    public function removeCompetence(Competence $competence): static
    {
        $this->competences->removeElement($competence);
        return $this;
    }

    // Méthode de compatibilité pour l'ancien code
    public function getCompetanceEquipe(): array
    {
        return $this->competences->map(fn($competence) => $competence->getNom())->toArray();
    }

    public function getNombre(): ?int { return $this->nombre; }
    public function setNombre(int $nombre): static { $this->nombre = $nombre; return $this; }

    public function getChefEquipe(): ?Ouvrier { return $this->chef_equipe; }
    public function setChefEquipe(?Ouvrier $chef_equipe): static { $this->chef_equipe = $chef_equipe; return $this; }

    /**
     * @return Collection<int, Ouvrier>
     */
    public function getOuvriers(): Collection { return $this->ouvriers; }

    public function addOuvrier(Ouvrier $ouvrier): static 
    {
        if (!$this->ouvriers->contains($ouvrier)) {
            $this->ouvriers->add($ouvrier);
            $ouvrier->setEquipe($this);
        }
        return $this;
    }

    public function removeOuvrier(Ouvrier $ouvrier): static 
    {
        if ($this->ouvriers->removeElement($ouvrier) && $ouvrier->getEquipe() === $this) {
            $ouvrier->setEquipe(null);
        }
        return $this;
    }

    /**
     * @return Collection<int, Affectation>
     */
    public function getAffectations(): Collection { return $this->affectations; }

    public function addAffectation(Affectation $affectation): static 
    {
        if (!$this->affectations->contains($affectation)) {
            $this->affectations->add($affectation);
            $affectation->setEquipe($this);
        }
        return $this;
    }

    public function removeAffectation(Affectation $affectation): static 
    {
        if ($this->affectations->removeElement($affectation) && $affectation->getEquipe() === $this) {
            $affectation->setEquipe(null);
        }
        return $this;
    }
}
