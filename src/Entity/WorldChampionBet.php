<?php

namespace App\Entity;

use App\Repository\WorldChampionBetRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorldChampionBetRepository::class)]
class WorldChampionBet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'worldChampionBets')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Driver $driver = null;

    #[ORM\ManyToOne(inversedBy: 'worldChampionBets')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'worldChampionBets')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Season $season = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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
