<?php

namespace App\ScoreCalculation;

use App\Entity\PenaltyPointsAward;
use App\Entity\Race;
use App\Entity\RaceResult;
use App\Entity\RaceResultBet;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class RaceScoreCalculator
{
    /**
     * @var Collection<int, RaceResultBetScoreCalculator>
     */
    private Collection $raceResultBetScoreCalculators;
    private ?int $score;
    private ?PenaltyPointsAward $penaltyPointsAward;

    public function __construct(
        private Race $race,
        private User $user,
        private bool $isScoreDiscarded = false,
        private float $bestOfTheRaceScoreShare = 0
    ) {
        $this->raceResultBetScoreCalculators = new ArrayCollection();
        $this->generateRaceResultBetScoreCalculators();
        $this->penaltyPointsAward = $this->findPenaltyPointsAwardOfUser();
        $this->score = $this->calculateScore();
    }

    public function getRace(): Race
    {
        return $this->race;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getIsScoreDiscarded(): bool
    {
        return $this->isScoreDiscarded;
    }

    public function setIsScoreDiscarded(bool $isScoreDiscarded): void
    {
        $this->isScoreDiscarded = $isScoreDiscarded;
    }

    public function getBestOfTheRaceScoreShare(): float
    {
        return $this->bestOfTheRaceScoreShare;
    }

    public function setBestOfTheRaceScoreShare(float $bestOfTheRaceScoreShare): void
    {
        $this->bestOfTheRaceScoreShare = $bestOfTheRaceScoreShare;
    }

    public function getRaceResultBetScoreCalculators(): Collection
    {
        return $this->raceResultBetScoreCalculators;
    }

    private function generateRaceResultBetScoreCalculators(): void {
        $this->raceResultBetScoreCalculators->clear();

        /** @var Collection<int, RaceResultBet> $raceResultBetsOfUser */
        $raceResultBetsOfUser = $this->getRaceResultBetsOfUser();

        /** @var RaceResultBet $raceResultBet */
        foreach ($raceResultBetsOfUser as $raceResultBet) {
            $raceResult = $this->getRaceResultForRaceResultBet($raceResultBet);
            $raceResultBetScoreCalculator = new RaceResultBetScoreCalculator($raceResultBet, $raceResult);
            $this->raceResultBetScoreCalculators[] = $raceResultBetScoreCalculator;
        }
    }

    private function getRaceResultForRaceResultBet(RaceResultBet $raceResultBet): ?RaceResult {
        $driver = $raceResultBet->getDriver();
        /** @var Collection<int, RaceResult> $raceResults */
        $raceResults = $this->race->getRaceResults();
        $raceResultsOfDriver = $raceResults->filter(function(RaceResult $raceResult) use ($driver) {
            return ($raceResult->getDriver()->getId() === $driver->getId());
        });

        return $raceResultsOfDriver->count() > 0 ? $raceResultsOfDriver->first() : null;
    }

    /**
     * @return Collection<int, RaceResultBet>
     */
    private function getRaceResultBetsOfUser(): Collection {
        /** @var Collection<int, RaceResultBet> $raceResultBets */
        $raceResultBets = $this->race->getRaceResultBets();
        $raceResultBetsOfUser = $raceResultBets->filter(function(RaceResultBet $raceResultBet) {
            return ($raceResultBet->getUser()->getId() === $this->user->getId());
        });

        return $raceResultBetsOfUser;
    }

    public function getPenaltyPointsAward(): ?PenaltyPointsAward {
        return $this->penaltyPointsAward;
    }

    private function findPenaltyPointsAwardOfUser(): ?PenaltyPointsAward {
        /** @var Collection<int, PenaltyPointsAward> $penaltyPointsAwards */
        $penaltyPointsAwards = $this->race->getPenaltyPointsAwards();
        $penaltyPointsAwardsOfUser = $penaltyPointsAwards->filter(
            function(PenaltyPointsAward $penaltyPointsAward) {
                return ($penaltyPointsAward->getUser()->getId() === $this->user->getId());
        });

        return $penaltyPointsAwardsOfUser->count() > 0 ? $penaltyPointsAwardsOfUser->first() : null;
    }

    public function getScore(): ?int {
        return $this->score;
    }

    private function calculateScore(): ?int {
        $score = null;

        // Add all scores from race result bets.
        foreach ($this->raceResultBetScoreCalculators as $raceResultBetScoreCalculator) {
            $raceResultBetScore = $raceResultBetScoreCalculator->getScore();

            if ($raceResultBetScore !== null) {
                $score = $score + $raceResultBetScore;
            }
        }

        // Add penalty points if they exist.
        $penaltyPointsAward = $this->getPenaltyPointsAward();
        if ($penaltyPointsAward) {
            $penaltyPoints = $penaltyPointsAward->getPenaltyPoints();

            if ($penaltyPoints !== null) {
                $score = $score + $penaltyPoints;
            }
        }

        return $score;
    }
}