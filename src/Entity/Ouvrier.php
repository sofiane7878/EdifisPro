<?php

namespace App\Entity;

use App\Repository\OuvrierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OuvrierRepository::class)]
class Ouvrier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom_ouvrier = null;

    #[ORM\ManyToMany(targetEntity: Competence::class, inversedBy: 'ouvriers')]
    #[ORM\JoinTable(name: 'ouvrier_competence')]
    private Collection $competences;

    #[ORM\Column(length: 50)]
    private ?string $role = null;


    #[ORM\ManyToOne(targetEntity: Equipe::class, inversedBy: "ouvriers")]
    #[ORM\JoinColumn(onDelete: "SET NULL")] // L'équipe peut être null si supprimée
    private ?Equipe $equipe = null;



    #[ORM\OneToOne(targetEntity: User::class, mappedBy: 'ouvrier')]
    private ?User $user = null;

    public function __construct()
    {
        $this->competences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomOuvrier(): ?string
    {
        return $this->nom_ouvrier;
    }

    public function setNomOuvrier(string $nom_ouvrier): self
    {
        $this->nom_ouvrier = $nom_ouvrier;
        return $this;
    }

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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getEquipe(): ?Equipe
    {
        return $this->equipe;
    }

    public function setEquipe(?Equipe $equipe): self
    {
        $this->equipe = $equipe;
        return $this;
    }



    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        // unset the owning side of the relation if necessary
        if ($user === null && $this->user !== null) {
            $this->user->setOuvrier(null);
        }

        // set the owning side of the relation if necessary
        if ($user !== null && $user->getOuvrier() !== $this) {
            $user->setOuvrier($this);
        }

        $this->user = $user;
        return $this;
    }
}
