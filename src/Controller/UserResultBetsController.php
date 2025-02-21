<?php

namespace App\Controller;

use App\Entity\Season;
use App\Entity\User;
use App\Repository\SeasonRepository;
use App\Repository\UserRepository;
use App\ScoreCalculation\RaceScoreCalculator;
use App\ScoreCalculation\ResultsForUser;
use App\ScoreCalculation\ScoreCalculationService;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_USER)]
class UserResultBetsController extends AbstractController
{
    public function __construct(
        private readonly SeasonRepository $seasonRepository,
        private readonly UserRepository $userRepository
    ) {
    }

    #[Route('/user-result-bets', name: 'app_user_result_bets_list', methods: ['GET'])]
    public function list(): Response
    {
        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return $this->render('userResultBets/createSeason.html.twig');
        }

        $season = $activeSeasons[0];

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
            return throw $this->createAccessDeniedException('Must be logged in for this operation');
        }

        $scoreCalculator = new ScoreCalculationService($this->seasonRepository, $this->userRepository, $season);

        return $this->render('userResultBets/list.html.twig', [
            'season' => $season,
            'resultsForSeason' => $scoreCalculator->getResultsForSeason()
        ]);
    }

    #[Route('/user-result-bets/{id}', name: 'app_user_result_bets_detail', methods: ['GET'])]
    public function detail(Request $request, $id): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
            return throw $this->createAccessDeniedException('Must be logged in for this operation');
        }

        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return $this->render('raceResultBets/createSeason.html.twig');
        }

        $season = $activeSeasons[0];
        $forUser = $this->userRepository->find($id);

        if (!$forUser) {
            return throw $this->createNotFoundException('This user does not exist');
        }

        $scoreCalculator = new ScoreCalculationService($this->seasonRepository, $this->userRepository, $season);
        $resultsForUser = $scoreCalculator->getResultsForUser($forUser);

        $now = new \DateTimeImmutable('now', new DateTimeZone('UTC'));
        $bettingTimeLimit = $this->getParameter('F1SONNTAG_BETTING_TIME_LIMIT_FROM_RACE_START');

        return $this->render('userResultBets/detail.html.twig', [
            'resultsForUser' => $resultsForUser,
            'forUser' => $forUser,
            'loggedInUser' => $user,
            'season' => $season,
            'now' => $now,
            'bettingTimeLimit' => $bettingTimeLimit
        ]);
    }
}
