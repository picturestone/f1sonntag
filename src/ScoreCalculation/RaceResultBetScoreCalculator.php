<?php

namespace App\ScoreCalculation;

use App\Entity\RaceResult;
use App\Entity\RaceResultBet;

class RaceResultBetScoreCalculator
{
    public function __construct(
        private RaceResultBet $raceResultBet,
        private ?RaceResult $raceResult = null
    ) {
    }

    public function getRaceResult(): ?RaceResult
    {
        return $this->raceResult;
    }

    public function setRaceResult(RaceResult $raceResult): void
    {
        $this->raceResult = $raceResult;
    }

    public function getRaceResultBet(): RaceResultBet
    {
        return $this->raceResultBet;
    }

    public function setRaceResultBet(RaceResultBet $raceResultBet): void
    {
        $this->raceResultBet = $raceResultBet;
    }

    /**
     * Returns the score or null if either raceResult or raceResultBet are not set.
     *
     * @return int|null
     */
    public function getScore(): ?int {
        $score = null;
        $racePosition = null;
        $betPosition = $this->raceResultBet->getPosition();

        if ($this->raceResult) {
            $racePosition = $this->raceResult->getPosition();
        }

        if ($racePosition !== null && $betPosition !== null) {
            $score = abs($racePosition - $betPosition);
        }

        return $score;
    }
}
