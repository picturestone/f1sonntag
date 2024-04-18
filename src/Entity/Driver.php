<?php

namespace App\Entity;

use App\Repository\DriverRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DriverRepository::class)]
class Driver
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'drivers')]
    private ?Team $team = null;

    /**
     * @var Collection<int, RaceResult>
     */
    #[ORM\OneToMany(targetEntity: RaceResult::class, mappedBy: 'driver')]
    private Collection $raceResults;

    /**
     * @var Collection<int, RaceResultBet>
     */
    #[ORM\OneToMany(targetEntity: RaceResultBet::class, mappedBy: 'driver')]
    private Collection $raceResultBets;

    /**
     * @var Collection<int, WorldChampionBet>
     */
    #[ORM\OneToMany(targetEntity: WorldChampionBet::class, mappedBy: 'driver')]
    private Collection $worldChampionBets;

    #[ORM\Column]
    private ?bool $isActive = true;

    /**
     * @var Collection<int, Season>
     */
    #[ORM\OneToMany(targetEntity: Season::class, mappedBy: 'worldChampion')]
    private Collection $worldChampionSeasons;

    public function __construct()
    {
        $this->raceResults = new ArrayCollection();
        $this->raceResultBets = new ArrayCollection();
        $this->worldChampionBets = new ArrayCollection();
        $this->worldChampionSeasons = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return Collection<int, RaceResult>
     */
    public function getRaceResults(): Collection
    {
        return $this->raceResults;
    }

    public function addRaceResult(RaceResult $raceResult): static
    {
        if (!$this->raceResults->contains($raceResult)) {
            $this->raceResults->add($raceResult);
            $raceResult->setDriver($this);
        }

        return $this;
    }

    public function removeRaceResult(RaceResult $raceResult): static
    {
        if ($this->raceResults->removeElement($raceResult)) {
            // set the owning side to null (unless already changed)
            if ($raceResult->getDriver() === $this) {
                $raceResult->setDriver(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RaceResultBet>
     */
    public function getRaceResultBets(): Collection
    {
        return $this->raceResultBets;
    }

    public function addRaceResultBet(RaceResultBet $raceResultBet): static
    {
        if (!$this->raceResultBets->contains($raceResultBet)) {
            $this->raceResultBets->add($raceResultBet);
            $raceResultBet->setDriver($this);
        }

        return $this;
    }

    public function removeRaceResultBet(RaceResultBet $raceResultBet): static
    {
        if ($this->raceResultBets->removeElement($raceResultBet)) {
            // set the owning side to null (unless already changed)
            if ($raceResultBet->getDriver() === $this) {
                $raceResultBet->setDriver(null);
            }
        }

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
            $worldChampionBet->setDriver($this);
        }

        return $this;
    }

    public function removeWorldChampionBet(WorldChampionBet $worldChampionBet): static
    {
        if ($this->worldChampionBets->removeElement($worldChampionBet)) {
            // set the owning side to null (unless already changed)
            if ($worldChampionBet->getDriver() === $this) {
                $worldChampionBet->setDriver(null);
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

    /**
     * @return Collection<int, Season>
     */
    public function getWorldChampionSeasons(): Collection
    {
        return $this->worldChampionSeasons;
    }

    public function addWorldChampionSeason(Season $worldChampionSeason): static
    {
        if (!$this->worldChampionSeasons->contains($worldChampionSeason)) {
            $this->worldChampionSeasons->add($worldChampionSeason);
            $worldChampionSeason->setWorldChampion($this);
        }

        return $this;
    }

    public function removeWorldChampionSeason(Season $worldChampionSeason): static
    {
        if ($this->worldChampionSeasons->removeElement($worldChampionSeason)) {
            // set the owning side to null (unless already changed)
            if ($worldChampionSeason->getWorldChampion() === $this) {
                $worldChampionSeason->setWorldChampion(null);
            }
        }

        return $this;
    }
}
