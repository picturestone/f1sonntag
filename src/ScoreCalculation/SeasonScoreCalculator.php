<?php

namespace App\ScoreCalculation;

use App\Entity\Race;
use App\Entity\Season;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * This class hides the complex score calculation. It gets all the data necessary, sets up calculators for each
 * individual bet, for each individual race and makes sure that the races are correctly marked as discarded and that the
 * "best of the race" score is calculated. Then it provides these race calculators according to if a perspective from
 * a single user to how they did in all races is needed, or from a single race with how all users did, or how the season
 * is going for all users.
 */
class SeasonScoreCalculator
{
    /** @var Collection<int, RaceScoreCalculator> raceScoreCalculators */
    private Collection $raceScoreCalculators;
    /** @var Collection<int, ResultsForUser) $resultsForUsers */
    private Collection $resultsForUsers;
    /** @var Collection<int, ResultsForRace) $resultsForRaces */
    private Collection $resultsForRaces;
    /** @var Collection<int, ResultForSeason) $resultsForSeason */
    private Collection $resultsForSeason;

    public function __construct(
        private Season $season,
        /** @var Collection<int, User> $users */
        private Collection $users
    ) {
        $this->resultsForUsers = new ArrayCollection();
        $this->resultsForRaces = new ArrayCollection();
        $this->resultsForSeason = new ArrayCollection();
        $this->raceScoreCalculators = $this->generateRaceScoreCalculators();
        $this->updateResultsForUsers();
        $this->updateResultsForRaces();
        $this->updateResultsForSeason();
    }

    public function getSeason(): Season
    {
        return $this->season;
    }

    public function getResultsForUser(User $user): ResultsForRace
    {
        return $this->resultsForUsers->get($user->getId());
    }

    public function getResultsForRace(Race $race): ResultsForRace
    {
        return $this->resultsForRaces->get($race->getId());
    }

    /** @return Collection<int, ResultForSeason> */
    public function getResultsForSeason(): Collection
    {
        return $this->resultsForSeason;
    }

    private function updateResultsForUsers(): void {
        $this->resultsForUsers->clear();

        foreach ($this->users as $user) {
            // Filter for race score calculators of user.
            $raceScoreCalculators = &$this->raceScoreCalculators;
            /** @var Collection<int, RaceScoreCalculator) $raceScoreCalculatorsOfUser */
            $raceScoreCalculatorsOfUser = $raceScoreCalculators->filter(
                function(RaceScoreCalculator $raceScoreCalculator) use ($user) {
                    return $raceScoreCalculator->getUser()->getId() === $user->getId();
                }
            );

            // Only add ResultsForUser if the user has raceScoreCalculators.
            if ($raceScoreCalculatorsOfUser->count() > 0) {
                $this->resultsForUsers->set($user->getId(), new ResultsForUser(
                    $user,
                    $raceScoreCalculatorsOfUser
                ));
            }
        }
    }

    private function updateResultsForRaces(): void {
        $this->resultsForRaces->clear();

        foreach ($this->season->getRaces() as $race) {
            // Filter for race score calculators of race.
            $raceScoreCalculators = &$this->raceScoreCalculators;
            /** @var Collection<int, RaceScoreCalculator) $raceScoreCalculatorsOfRace */
            $raceScoreCalculatorsOfRace = $raceScoreCalculators->filter(
                function(RaceScoreCalculator $raceScoreCalculator) use ($race) {
                    return $raceScoreCalculator->getRace()->getId() === $race->getId();
                }
            );

            // Only add ResultsForRace if the race has raceScoreCalculators.
            if ($raceScoreCalculatorsOfRace->count() > 0) {
                $this->resultsForRaces->set($race->getId(), new ResultsForRace(
                    $race,
                    $raceScoreCalculatorsOfRace
                ));
            }
        }
    }

    private function updateResultsForSeason(): void {
        $this->resultsForSeason->clear();

        // Generate all the results for the season.
        foreach ($this->resultsForUsers as $resultsForUser) {
            $user = $resultsForUser->getUser();
            $raceScoreCalculators = $resultsForUser->getRaceScoreCalculatorsOfUser();
            $this->resultsForSeason->set($user->getId(), new ResultForSeason($user, $raceScoreCalculators));
        }

        // Order the season results by score.
        $iterator = $this->resultsForSeason->getIterator();
        $iterator->uasort(function(ResultForSeason $a, ResultForSeason $b) {
            $scoreA = $a->getTotalScore();
            $scoreB = $b->getTotalScore();

            // If they have equal score, look at the "best of the race" scores.
            if ($scoreA === $scoreB) {
                $bestOfTheRaceScoreA = $a->getTotalBestOfTheRaceScore();
                $bestOfTheRaceScoreB = $b->getTotalBestOfTheRaceScore();

                if ($bestOfTheRaceScoreA == $bestOfTheRaceScoreB) return 0;

                // The one with more "best of the race" score is better.
                return ($bestOfTheRaceScoreA > $bestOfTheRaceScoreB) ? -1 : 1;
            }

            // Ihe one with less score is better.
            return ($scoreA < $scoreB) ? -1 : 1;
        });
        $this->resultsForSeason = new ArrayCollection(iterator_to_array($iterator));

        // TODO check if the key of the resultsForSeason collection is still the user id or if its now not the user id
        // anymore. Also check if sorting worked correctly.
    }

    /** @return Collection<int, RaceScoreCalculator> */
    private function generateRaceScoreCalculators(): Collection {
        /** @var Collection<int, RaceScoreCalculator> $raceScoreCalculators */
        $raceScoreCalculators = new ArrayCollection();

        // Generate all race score calculators for all users.
        $races = $this->season->getRaces();

        foreach ($races as $race) {
            foreach ($this->users as $user) {
                $raceScoreCalculator = new RaceScoreCalculator($race, $user);

                if ($raceScoreCalculator->getScore() !== null) {
                    // Only add the race score calculator if it is relevant for the scoring (by bet or by penalty
                    // points).
                    $raceScoreCalculators->add($raceScoreCalculator);
                }
            }
        }

        return $raceScoreCalculators;
    }
}
