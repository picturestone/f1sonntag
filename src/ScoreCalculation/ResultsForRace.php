<?php

namespace App\ScoreCalculation;

use App\Entity\Race;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Represents the betting that happend for a single race - so all users of one race for one season are considered here.
 * This is allows finding out who had the best scores of a race.
 */
class ResultsForRace
{
    /** @var Collection<int, RaceScoreCalculator> $raceScoreCalculatorsOfRace */
    private Collection $raceScoreCalculatorsOfRace;

    public function __construct(
        private Race $race,
        /** @var Collection<int, RaceScoreCalculator> $allRaceScoreCalculators */
        Collection &$raceScoreCalculatorsOfRace
    ) {
        $this->raceScoreCalculatorsOfRace = new ArrayCollection();
        $this->updateRaceScoreCalculatorsDailyWinScoreData($raceScoreCalculatorsOfRace);
    }

    public function getRaceScoreCalculatorsOfRace(): Collection
    {
        return $this->raceScoreCalculatorsOfRace;
    }

    public function getRace(): Race
    {
        return $this->race;
    }

    /**
     * @param Collection<int, RaceScoreCalculator> $raceScoreCalculatorsOfRace
     * @return void
     */
    private function updateRaceScoreCalculatorsDailyWinScoreData(Collection &$raceScoreCalculatorsOfRace): void {
        $this->raceScoreCalculatorsOfRace = $raceScoreCalculatorsOfRace;

        // Order the race score calculators of this race so we can find the lowest score.
        /** @var Collection<int, RaceScoreCalculator> $scoreSortedRaceScoreCalculators */
        $scoreSortedRaceScoreCalculators = new ArrayCollection();

        foreach ($this->raceScoreCalculatorsOfRace as $raceScoreCalculator) {
            if ($raceScoreCalculator->getScore() !== null) {
                $scoreSortedRaceScoreCalculators->add($raceScoreCalculator);
            }
        }

        $iterator = $scoreSortedRaceScoreCalculators->getIterator();
        $iterator->uasort(function(RaceScoreCalculator $a, RaceScoreCalculator $b) {
            $scoreA = $a->getScore();
            $scoreB = $b->getScore();

            if ($scoreA === $scoreB) return 0;
            return ($scoreA < $scoreB) ? -1 : 1;
        });
        $scoreSortedRaceScoreCalculators = new ArrayCollection(iterator_to_array($iterator));

        // Find the lowest score and all the race score calculators who share the lowest score.
        $raceScoreCalculatorsWithBestScore = $scoreSortedRaceScoreCalculators->first();

        if ($raceScoreCalculatorsWithBestScore) {
            $bestScore = $raceScoreCalculatorsWithBestScore->getScore();
            $raceScoreCalculatorsWithBestScore = $this->filterRaceScoreCalculatorsByScore(
                $this->raceScoreCalculatorsOfRace,
                $bestScore
            );

            // If multiple bets on a race share the winning score then the daily win score is split among them.
            $scoreForDailyWin = 1 / count($raceScoreCalculatorsWithBestScore);

            // Set the best of the race score on each race score calculator.
            foreach ($raceScoreCalculatorsWithBestScore as $raceScoreCalculatorWithBestScore) {
                $raceScoreCalculatorWithBestScore->setBestOfTheRaceScoreShare($scoreForDailyWin);
            }
        }
    }

    /**
     * @param Collection<int, RaceScoreCalculator> $raceScoreCalculators
     * @return Collection<int, RaceScoreCalculator>
     */
    private function filterRaceScoreCalculatorsByScore(Collection &$raceScoreCalculators, int $score): Collection
    {
        /** @var Collection<int, RaceScoreCalculator> $raceScoreCalculatorsByScore */
        $raceScoreCalculatorsByScore = $raceScoreCalculators->filter(
            function(RaceScoreCalculator $raceScoreCalculator) use ($score) {
                return $raceScoreCalculator->getScore() === $score;
            }
        );

        return $raceScoreCalculatorsByScore;
    }
}
