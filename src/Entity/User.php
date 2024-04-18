<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, RaceResultBet>
     */
    #[ORM\OneToMany(targetEntity: RaceResultBet::class, mappedBy: 'user')]
    private Collection $raceResultBets;

    /**
     * @var Collection<int, PenaltyPointsAward>
     */
    #[ORM\OneToMany(targetEntity: PenaltyPointsAward::class, mappedBy: 'user')]
    private Collection $penaltyPointsAward;

    /**
     * @var Collection<int, WorldChampionBet>
     */
    #[ORM\OneToMany(targetEntity: WorldChampionBet::class, mappedBy: 'user')]
    private Collection $worldChampionBets;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column]
    private ?bool $isActive = true;

    public function __construct()
    {
        $this->raceResultBets = new ArrayCollection();
        $this->penaltyPointsAward = new ArrayCollection();
        $this->worldChampionBets = new ArrayCollection();
    }

    final public const ROLE_USER = 'ROLE_USER';
    final public const ROLE_ADMIN = 'ROLE_ADMIN';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = User::ROLE_USER;

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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
            $raceResultBet->setUser($this);
        }

        return $this;
    }

    public function removeRaceResultBet(RaceResultBet $raceResultBet): static
    {
        if ($this->raceResultBets->removeElement($raceResultBet)) {
            // set the owning side to null (unless already changed)
            if ($raceResultBet->getUser() === $this) {
                $raceResultBet->setUser(null);
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
            $penaltyPointsAward->setUser($this);
        }

        return $this;
    }

    public function removePenaltyPointsAward(PenaltyPointsAward $penaltyPointsAward): static
    {
        if ($this->penaltyPointsAward->removeElement($penaltyPointsAward)) {
            // set the owning side to null (unless already changed)
            if ($penaltyPointsAward->getUser() === $this) {
                $penaltyPointsAward->setUser(null);
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
            $worldChampionBet->setUser($this);
        }

        return $this;
    }

    public function removeWorldChampionBet(WorldChampionBet $worldChampionBet): static
    {
        if ($this->worldChampionBets->removeElement($worldChampionBet)) {
            // set the owning side to null (unless already changed)
            if ($worldChampionBet->getUser() === $this) {
                $worldChampionBet->setUser(null);
            }
        }

        return $this;
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
