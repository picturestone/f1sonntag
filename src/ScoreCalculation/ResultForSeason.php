<?php

namespace App\ScoreCalculation;

use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Adds up stats for the season for one user - so all races of one user for one season are considered here.
 */
class ResultForSeason
{
    private ?float $totalBestOfTheRaceScore = null;
    private ?int $totalScoreWithoutPenaltyPoints = null;
    private ?int $totalPenaltyPoints = null;
    /** @var Collection<int, RaceScoreCalculator> */
    private Collection $discardedResults;
    private ?int $totalScore = null;

    public function __construct(
        private User $user,
        /** @var Collection<int, RaceScoreCalculator> $raceScoreCalculatorsOfUser */
        Collection $raceScoreCalculatorsOfUser
    ) {
        $this->discardedResults = new ArrayCollection();
        $this->extractData($raceScoreCalculatorsOfUser);
        $this->orderDiscardedResultsByScore();
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getTotalBestOfTheRaceScore(): ?float
    {
        return $this->totalBestOfTheRaceScore;
    }

    public function getTotalScoreWithoutPenaltyPoints(): ?int
    {
        return $this->totalScoreWithoutPenaltyPoints;
    }

    public function getTotalPenaltyPoints(): ?int
    {
        return $this->totalPenaltyPoints;
    }

    public function getDiscardedResults(): Collection
    {
        return $this->discardedResults;
    }

    public function getTotalScore(): ?int
    {
        return $this->totalScore;
    }

    /**
     * Adds up all the numbers from the race score calculators and gathers the discarded results of the user.
     *
     * @param Collection<int, RaceScoreCalculator> $raceScoreCalculatorsOfUser
     * @return void
     */
    private function extractData(Collection $raceScoreCalculatorsOfUser): void
    {
        /** @var RaceScoreCalculator $raceScoreCalculator */
        foreach ($raceScoreCalculatorsOfUser as $raceScoreCalculator) {
            // Add up best of the race score share of the race.
            $bestOfTheRaceScoreShare = $raceScoreCalculator->getBestOfTheRaceScoreShare();
            $this->totalBestOfTheRaceScore = $bestOfTheRaceScoreShare !== null
                ? $this->totalBestOfTheRaceScore + $bestOfTheRaceScoreShare
                : $this->totalBestOfTheRaceScore;

            // Add up total score without penalty points.
            $scoreWithoutPenaltyPoints = $raceScoreCalculator->getScoreWithoutPenaltyPoints();
            $this->totalScoreWithoutPenaltyPoints = $scoreWithoutPenaltyPoints !== null
                ? $this->totalScoreWithoutPenaltyPoints + $scoreWithoutPenaltyPoints
                : $this->totalScoreWithoutPenaltyPoints;

            // Add up total penalty points.
            $penaltyPoints = $raceScoreCalculator->getPenaltyPoints();
            $this->totalPenaltyPoints = $penaltyPoints !== null
                ? $this->totalPenaltyPoints + $penaltyPoints
                : $this->totalPenaltyPoints;

            // Add up total score.
            $score = $raceScoreCalculator->getScore();
            $this->totalScore = $score !== null
                ? $this->totalScore + $score
                : $this->totalScore;

            // Add race calculator to discarded results if it is a discarded result.
            if ($raceScoreCalculator->isScoreDiscarded()) {
                $this->discardedResults->add($raceScoreCalculator);
            }
        }
    }

    /** Orders the discarded results by score (descending) so they can be displayed in order. */
    private function orderDiscardedResultsByScore(): void {
        $iterator = $this->discardedResults->getIterator();
        $iterator->uasort(function(RaceScoreCalculator $a, RaceScoreCalculator $b) {
            $scoreA = $a->getScore();
            $scoreB = $b->getScore();

            if ($scoreA === $scoreB) return 0;
            return ($scoreA > $scoreB) ? -1 : 1;
        });
        $this->discardedResults = new ArrayCollection(iterator_to_array($iterator));
    }
}
