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

    #[ORM\ManyToOne(inversedBy: 'drivers')]
    private ?Team $team = null;

    /**
     * @var Collection<int, RaceResult>
     */
    #[ORM\OneToMany(targetEntity: RaceResult::class, mappedBy: 'driver')]
    private Collection $raceResults;

    /**
     * @var Collection<int, PositionBet>
     */
    #[ORM\OneToMany(targetEntity: PositionBet::class, mappedBy: 'driver')]
    private Collection $positionBets;

    /**
     * @var Collection<int, WorldChampionBet>
     */
    #[ORM\OneToMany(targetEntity: WorldChampionBet::class, mappedBy: 'driver')]
    private Collection $worldChampionBets;

    #[ORM\Column]
    private ?bool $isActive = true;

    public function __construct()
    {
        $this->raceResults = new ArrayCollection();
        $this->positionBets = new ArrayCollection();
        $this->worldChampionBets = new ArrayCollection();
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
     * @return Collection<int, PositionBet>
     */
    public function getPositionBets(): Collection
    {
        return $this->positionBets;
    }

    public function addPositionBet(PositionBet $positionBet): static
    {
        if (!$this->positionBets->contains($positionBet)) {
            $this->positionBets->add($positionBet);
            $positionBet->setDriver($this);
        }

        return $this;
    }

    public function removePositionBet(PositionBet $positionBet): static
    {
        if ($this->positionBets->removeElement($positionBet)) {
            // set the owning side to null (unless already changed)
            if ($positionBet->getDriver() === $this) {
                $positionBet->setDriver(null);
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
}
