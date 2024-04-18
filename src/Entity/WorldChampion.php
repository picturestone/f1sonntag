<?php

namespace App\Entity;

use App\Repository\WorldChampionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorldChampionRepository::class)]
class WorldChampion
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'worldChampions')]
    private ?Driver $driver = null;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'worldChampions')]
    private ?Season $season = null;

    public function getDriver(): ?Driver
    {
        return $this->driver;
    }

    public function setDriver(?Driver $driver): static
    {
        $this->driver = $driver;

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
