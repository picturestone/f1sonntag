<?php

namespace App\Entity;

use App\Repository\SeasonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeasonRepository::class)]
class Season
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, WorldChampionBet>
     */
    #[ORM\OneToMany(targetEntity: WorldChampionBet::class, mappedBy: 'season')]
    private Collection $worldChampionBets;

    /**
     * @var Collection<int, Race>
     */
    #[ORM\OneToMany(targetEntity: Race::class, mappedBy: 'season')]
    private Collection $races;

    #[ORM\Column]
    private ?bool $isActive = false;

    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'worldChampionSeasons')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Driver $worldChampion = null;

    public function __construct()
    {
        $this->worldChampionBets = new ArrayCollection();
        $this->races = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, WorldChampionBet>
     */
    public function getWorldChampionBets(): Collection
    {
        return $this->worldChampionBets;
    }

    public function addWorldChampionBet(WorldChampionBet $worldChampionBet): static
    {
        if (!$this->worldChampionBets->contains($worldChampionBet)) {
            $this->worldChampionBets->add($worldChampionBet);
            $worldChampionBet->setSeason($this);
        }

        return $this;
    }

    public function removeWorldChampionBet(WorldChampionBet $worldChampionBet): static
    {
        if ($this->worldChampionBets->removeElement($worldChampionBet)) {
            // set the owning side to null (unless already changed)
            if ($worldChampionBet->getSeason() === $this) {
                $worldChampionBet->setSeason(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Race>
     */
    public function getRaces(): Collection
    {
        return $this->races;
    }

    public function addRace(Race $race): static
    {
        if (!$this->races->contains($race)) {
            $this->races->add($race);
            $race->setSeason($this);
        }

        return $this;
    }

    public function removeRace(Race $race): static
    {
        if ($this->races->removeElement($race)) {
            // set the owning side to null (unless already changed)
            if ($race->getSeason() === $this) {
                $race->setSeason(null);
            }
        }

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getWorldChampion(): ?Driver
    {
        return $this->worldChampion;
    }

    public function setWorldChampion(?Driver $worldChampion): static
    {
        $this->worldChampion = $worldChampion;

        return $this;
    }
}
