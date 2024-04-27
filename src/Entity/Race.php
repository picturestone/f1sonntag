<?php

namespace App\Entity;

use App\Repository\RaceRepository;
use DateTimeZone;
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
    private Collection $penaltyPointsAwards;

    #[ORM\ManyToOne(inversedBy: 'races')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Season $season = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $startDateTime = null;

    public function __construct()
    {
        $this->raceResults = new ArrayCollection();
        $this->raceResultBets = new ArrayCollection();
        $this->penaltyPointsAwards = new ArrayCollection();
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
        $format = 'Y-m-d';
        $date = $this->getStartDateTime()->format($format);
        return \DateTimeImmutable::createFromFormat($format, $date);
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $format = 'Y-m-d';
        $this->startDateTime = $this->getStartDateTime()->modify($startDate->format($format));

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        $format = 'H:i:s';
        $date = $this->getStartDateTime()->format($format);
        return \DateTimeImmutable::createFromFormat($format, $date);
    }

    public function setStartTime(\DateTimeInterface $startTime): static
    {
        $format = 'H:i:s';
        $this->startDateTime = $this->getStartDateTime()->modify($startTime->format($format));

        return $this;
    }

    public function getStartDateTime(): ?\DateTimeImmutable
    {
        $startDateTime = $this->startDateTime;
        if ($startDateTime === null) {
            $startDateTime = new \DateTimeImmutable('now', new DateTimeZone('UTC'));
        }
        return $startDateTime;
    }

    public function setStartDateTime(\DateTimeImmutable $startDateTime): static
    {
        $this->startDateTime = $startDateTime;

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
    public function getPenaltyPointsAwards(): Collection
    {
        return $this->penaltyPointsAwards;
    }

    public function addPenaltyPointsAward(PenaltyPointsAward $penaltyPointsAward): static
    {
        if (!$this->penaltyPointsAwards->contains($penaltyPointsAward)) {
            $this->penaltyPointsAwards->add($penaltyPointsAward);
            $penaltyPointsAward->setRace($this);
        }

        return $this;
    }

    public function removePenaltyPointsAward(PenaltyPointsAward $penaltyPointsAward): static
    {
        if ($this->penaltyPointsAwards->removeElement($penaltyPointsAward)) {
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

    /**
     * Changes the interpretation of the start date time to a different timezone, so 2024-04-26 07:47:00 UCT can be
     * changed to 2024-04-26 07:47:00 CET (instead of also changing the date time to 2024-04-26 09:47:00 CET).
     *
     * @param DateTimeZone $dateTimeZone
     * @return $this
     * @throws \Exception
     */
    public function setStartDateTimeTimezoneWithoutChangingDateTime(DateTimeZone $dateTimeZone): static
    {
        $format = 'Y-m-d H:i:s';
        $startDateTimeString = $this->getStartDateTime()->format($format);
        $this->startDateTime = new \DateTimeImmutable($startDateTimeString, $dateTimeZone);

        return $this;
    }
}
