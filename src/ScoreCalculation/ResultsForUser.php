<?php

namespace App\ScoreCalculation;

use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ResultsForUser
{
    // TODO consider making this configurable in yaml.
    final public const NO_OF_DISCARDED_RACES = 2;

    /** @var Collection<int, RaceScoreCalculator> $raceScoreCalculatorsOfUser */
    private Collection $raceScoreCalculatorsOfUser;

    public function __construct(
        private User $user,
        /** @var Collection<int, RaceScoreCalculator> $allRaceScoreCalculators */
        Collection &$allRaceScoreCalculators
    ) {
        $this->raceScoreCalculatorsOfUser = new ArrayCollection();
        $this->updateRaceScoreCalculatorsDiscardData($allRaceScoreCalculators);
    }

    public function getRaceScoreCalculatorsOfUser(): Collection
    {
        return $this->raceScoreCalculatorsOfUser;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param Collection<int, RaceScoreCalculator> $allRaceScoreCalculators
     * @return void
     */
    private function updateRaceScoreCalculatorsDiscardData(Collection &$allRaceScoreCalculators): void {
        // Filter all race score calculators
        $this->raceScoreCalculatorsOfUser = $this->filterRaceScoreCalculatorsByUser($allRaceScoreCalculators);

        if ($this->raceScoreCalculatorsOfUser->count() === 0) {
            return;
        }

        // Find the discarded race score calculators from the users race score calculators..
        /** @var Collection<int, RaceScoreCalculator> $scoreSortedRaceScoreCalculators */
        $scoreSortedRaceScoreCalculators = new ArrayCollection();

        foreach ($this->raceScoreCalculatorsOfUser as $raceScoreCalculator) {
            $scoreSortedRaceScoreCalculators->add($raceScoreCalculator);
        }

        $iterator = $scoreSortedRaceScoreCalculators->getIterator();
        $iterator->uasort(function(RaceScoreCalculator $a, RaceScoreCalculator $b) {
            $scoreA = $a->getScore();
            $scoreB = $b->getScore();

            if ($scoreA === $scoreB) return 0;
            return ($scoreA > $scoreB) ? -1 : 1;
        });
        $scoreSortedRaceScoreCalculators = new ArrayCollection(iterator_to_array($iterator));

        $noOfRacesToDiscard = ResultsForUser::NO_OF_DISCARDED_RACES;

        if ($scoreSortedRaceScoreCalculators->count() > $noOfRacesToDiscard) {
            // We only discard races if there are more races than races we discard. This way, we also have scores at
            // the start of the season and not only after the $noOfRacesToDiscard'th race.
            $raceScoreCalculatorsToDiscard = $scoreSortedRaceScoreCalculators->slice(
                0,
                $noOfRacesToDiscard
            );

            foreach ($raceScoreCalculatorsToDiscard as $raceScoreCalculatorToDiscard) {
                $raceScoreCalculatorToDiscard->setIsScoreDiscarded(true);
            }
        }
    }

    /**
     * @param Collection<int, RaceScoreCalculator) $allRaceScoreCalculators
     * @return Collection<int, RaceScoreCalculator)
     */
    private function filterRaceScoreCalculatorsByUser(Collection &$allRaceScoreCalculators): Collection
    {
        /** @var Collection<int, RaceScoreCalculator) $raceScoreCalculatorsOfUser */
        $raceScoreCalculatorsOfUser = $allRaceScoreCalculators->filter(
            function(RaceScoreCalculator $raceScoreCalculator) {
                return $raceScoreCalculator->getUser()->getId() === $this->user->getId();
            });

        return $raceScoreCalculatorsOfUser;
    }
}
