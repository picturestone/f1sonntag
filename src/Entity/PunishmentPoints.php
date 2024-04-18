<?php

namespace App\Entity;

use App\Repository\PunishmentPointsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PunishmentPointsRepository::class)]
class PunishmentPoints
{
    #[ORM\Column]
    private ?int $penaltyPoints = 0;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'punishmentPoints')]
    private ?User $user = null;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'punishmentPoints')]
    private ?Race $race = null;

    public function getPenaltyPoints(): ?int
    {
        return $this->penaltyPoints;
    }

    public function setPenaltyPoints(int $penaltyPoints): static
    {
        $this->penaltyPoints = $penaltyPoints;

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
