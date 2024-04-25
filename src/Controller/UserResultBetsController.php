<?php

namespace App\Controller;

use App\Dto\ToastDto;
use App\Entity\Race;
use App\Entity\RaceResultBet;
use App\Entity\Season;
use App\Entity\User;
use App\Repository\DriverRepository;
use App\Repository\RaceRepository;
use App\Repository\RaceResultBetRepository;
use App\Repository\SeasonRepository;
use App\Repository\UserRepository;
use App\ScoreCalculation\ResultsForRace;
use App\ScoreCalculation\ScoreCalculationService;
use App\Service\ToastFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
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


        return $this->render('userResultBets/edit.html.twig', [
            'form' => $form,
            'raceResultBets' => $raceResultBets,
            'race' => $race,
            'season' => $season
        ]);
    }
}
