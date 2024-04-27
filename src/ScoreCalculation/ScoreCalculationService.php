<?php

namespace App\ScoreCalculation;

use App\Entity\Race;
use App\Entity\Season;
use App\Entity\User;
use App\Repository\SeasonRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * This is the interface provided to the outside regardings core calculation. It provides methods to get scores for
 * various situations:
 * - for a race and how well all the users did in the race
 * - for a user and how well they did in all the individual races
 * - for an overview of how well every user did in the season
 *
 * To provide that, the calculation service grabs the data it needs from the database.
 */
class ScoreCalculationService
{
    public function __construct(
        private readonly SeasonRepository $seasonRepository,
        private readonly UserRepository $userRepository,
        private readonly Season $season
    ) {
    }

    public function getResultsForRace(Race $race): ?ResultsForRace
    {
        $calculator = $this->createSeasonScoreCalculator();

        return $calculator->getResultsForRace($race);
    }

    public function getResultsForUser(User $user): ResultsForUser {
        $calculator = $this->createSeasonScoreCalculator();

        return $calculator->getResultsForUser($user);
    }

    /**
     * @return Collection<int, ResultForSeason>
     */
    public function getResultsForSeason(): Collection {
        $calculator = $this->createSeasonScoreCalculator();

        return $calculator->getResultsForSeason();
    }

    private function createSeasonScoreCalculator(): SeasonScoreCalculator {
        $seasonToScore = $this->seasonRepository->findSeasonWithDataForScores($this->season->getId());
        /** @var Collection<int, User> $users */
        $users = new ArrayCollection($this->userRepository->findAll());
        $calculator = new SeasonScoreCalculator($seasonToScore, $users);

        return $calculator;
    }
}
