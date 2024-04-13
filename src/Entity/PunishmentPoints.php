<?php

namespace App\Entity;

use App\Repository\PunishmentPointsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PunishmentPointsRepository::class)]
class PunishmentPoints
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $punishmentPoints = null;

    #[ORM\ManyToOne(inversedBy: 'punishmentPoints')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'punishmentPoints')]
    private ?Race $race = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPunishmentPoints(): ?int
    {
        return $this->punishmentPoints;
    }

    public function setPunishmentPoints(int $punishmentPoints): static
    {
        $this->punishmentPoints = $punishmentPoints;

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
