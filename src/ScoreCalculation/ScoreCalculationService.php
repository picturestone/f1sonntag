<?php

namespace App\ScoreCalculation;

use App\Entity\Race;
use App\Entity\Season;
use App\Entity\User;
use App\Repository\SeasonRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ScoreCalculationService
{
    public function __construct(
        private readonly SeasonRepository $seasonRepository,
        private readonly UserRepository $userRepository,
        private readonly Season $season
    ) {
    }

    public function getResultsForRace(Race $race): ResultsForRace
    {
        $seasonToScore = $this->seasonRepository->findSeasonWithDataForScores($this->season->getId());
        /** @var Collection<int, User> $users */
        $users = new ArrayCollection($this->userRepository->findAll());
        $calculator = new SeasonScoreCalculator($seasonToScore, $users);

        return $calculator->getResultsForRace($race);
    }

    public function getResultsForUser(User $user) {

    }

    public function getResultsForSeason() {

    }
}
