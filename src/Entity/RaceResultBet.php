<?php

namespace App\Entity;

use App\Repository\RaceResultBetRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RaceResultBetRepository::class)]
class RaceResultBet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $position = null;

    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'raceResultBets')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'raceResultBets')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Driver $driver = null;

    #[ORM\ManyToOne(inversedBy: 'raceResultBets')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Race $race = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getDriver(): ?Driver
    {
        return $this->driver;
    }

    public function setDriver(?Driver $driver): static
    {
        $this->driver = $driver;

        return $this;
    }

    public function getRace(): ?Race
    {
        return $this->race;
    }

    public function setRace(?Race $race): static
    {
        $this->race = $race;

        return $this;
    }
}
