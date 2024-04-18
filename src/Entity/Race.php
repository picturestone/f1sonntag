<?php

namespace App\Entity;

use App\Repository\RaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RaceRepository::class)]
class Race
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $place = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $startTime = null;

    /**
     * @var Collection<int, RaceResult>
     */
    #[ORM\OneToMany(targetEntity: RaceResult::class, mappedBy: 'race')]
    private Collection $raceResults;

    /**
     * @var Collection<int, RaceResultBet>
     */
    #[ORM\OneToMany(targetEntity: RaceResultBet::class, mappedBy: 'race')]
    private Collection $raceResultBets;

    /**
     * @var Collection<int, PenaltyPointsAward>
     */
    #[ORM\OneToMany(targetEntity: PenaltyPointsAward::class, mappedBy: 'race')]
    private Collection $penaltyPointsAward;

    #[ORM\ManyToOne(inversedBy: 'races')]
    private ?Season $season = null;

    public function __construct()
    {
        $this->raceResults = new ArrayCollection();
        $this->raceResultBets = new ArrayCollection();
        $this->penaltyPointsAward = new ArrayCollection();
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

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(string $place): static
    {
        $this->place = $place;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): static
    {
        $this->startTime = $startTime;

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
            $raceResult->setRace($this);
        }

        return $this;
    }

    public function removeRaceResult(RaceResult $raceResult): static
    {
        if ($this->raceResults->removeElement($raceResult)) {
            // set the owning side to null (unless already changed)
            if ($raceResult->getRace() === $this) {
                $raceResult->setRace(null);
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
            $raceResultBet->setRace($this);
        }

        return $this;
    }

    public function removeRaceResultBet(RaceResultBet $raceResultBet): static
    {
        if ($this->raceResultBets->removeElement($raceResultBet)) {
            // set the owning side to null (unless already changed)
            if ($raceResultBet->getRace() === $this) {
                $raceResultBet->setRace(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PenaltyPointsAward>
     */
    public function getPenaltyPointsAward(): Collection
    {
        return $this->penaltyPointsAward;
    }

    public function addPenaltyPointsAward(PenaltyPointsAward $penaltyPointsAward): static
    {
        if (!$this->penaltyPointsAward->contains($penaltyPointsAward)) {
            $this->penaltyPointsAward->add($penaltyPointsAward);
            $penaltyPointsAward->setRace($this);
        }

        return $this;
    }

    public function removePenaltyPointsAward(PenaltyPointsAward $penaltyPointsAward): static
    {
        if ($this->penaltyPointsAward->removeElement($penaltyPointsAward)) {
            // set the owning side to null (unless already changed)
            if ($penaltyPointsAward->getRace() === $this) {
                $penaltyPointsAward->setRace(null);
            }
        }

        return $this;
    }

    public function getSeason(): ?Season
    {
        return $this->season;
    }

    public function setSeason(?Season $season): static
    {
        $this->season = $season;

        return $this;
    }
}
