<?php

namespace App\Entity;

use App\Repository\PenaltyPointsAwardRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PenaltyPointsAwardRepository::class)]
class PenaltyPointsAward
{
    #[ORM\Column]
    private ?int $penaltyPoints = 0;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'penaltyPointsAward')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'penaltyPointsAward')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
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
