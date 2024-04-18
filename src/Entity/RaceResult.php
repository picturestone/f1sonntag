<?php

namespace App\Entity;

use App\Repository\RaceResultRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RaceResultRepository::class)]
class RaceResult
{
    #[ORM\Column]
    private ?int $position = 0;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'raceResults')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Driver $driver = null;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'raceResults')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Race $race = null;

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

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
