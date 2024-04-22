<?php

namespace App\ScoreCalculation;

use App\Entity\Race;
use App\Entity\Season;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class SeasonScoreCalculator
{
    /** @var Collection<int, RaceScoreCalculator> raceScoreCalculators */
    private Collection $raceScoreCalculators;
    /** @var Collection<int, ResultsForUser) $resultsForUsers */
    private Collection $resultsForUsers;
    /** @var Collection<int, ResultsForRace) $resultsForRaces */
    private Collection $resultsForRaces;

    public function __construct(
        private Season $season,
        /** @var Collection<int, User> $users */
        private Collection $users
    ) {
        $this->resultsForUsers = new ArrayCollection();
        $this->resultsForRaces = new ArrayCollection();
        $this->raceScoreCalculators = $this->generateRaceScoreCalculators();
        $this->updateResultsForUsers();
        $this->updateResultsForRaces();
    }

    public function getSeason(): Season
    {
        return $this->season;
    }

    public function getResultsForUser(): Collection
    {
        return $this->resultsForUsers;
    }

    public function getResultsForRace(Race $race): ResultsForRace
    {
        return $this->resultsForRaces[$race->getId()];
    }

    private function updateResultsForUsers(): void {
        $this->resultsForUsers->clear();

        foreach ($this->users as $user) {
            $this->resultsForUsers->set($user->getId(), new ResultsForUser(
                $user,
                $this->raceScoreCalculators
            ));
        }
    }

    private function updateResultsForRaces(): void {
        $this->resultsForRaces->clear();

        foreach ($this->season->getRaces() as $race) {
            $this->resultsForRaces->set($race->getId(), new ResultsForRace(
                $race,
                $this->raceScoreCalculators
            ));
        }
    }

    /** @return Collection<int, RaceScoreCalculator> */
    private function generateRaceScoreCalculators(): Collection {
        /** @var Collection<int, RaceScoreCalculator> $raceScoreCalculators */
        $raceScoreCalculators = new ArrayCollection();

        // Generate all race score calculators for all users.
        $races = $this->season->getRaces();

        foreach ($races as $race) {
            foreach ($this->users as $user) {
                $raceScoreCalculators->add(new RaceScoreCalculator($race, $user));
            }
        }

        return $raceScoreCalculators;
    }
}
