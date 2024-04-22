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
class RaceResultBetsController extends AbstractController
{
    // TODO consider making this configurable in yaml.
    final public const NO_OF_BETS_PER_USER_PER_RACE = 3;

    public function __construct(
        private readonly EntityManagerInterface  $entityManager,
        private readonly SeasonRepository $seasonRepository,
        private readonly UserRepository $userRepository,
        private readonly RaceResultBetRepository $raceResultBetRepository,
        private readonly RaceRepository $raceRepository,
        private readonly DriverRepository $driverRepository
    ) {
    }

    #[Route('/race-result-bets', name: 'app_race_result_bets_list', methods: ['GET'])]
    public function list(): Response
    {
        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return $this->render('raceResultBets/createSeason.html.twig');
        }

        $season = $activeSeasons[0];

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
            return throw $this->createAccessDeniedException('Must be logged in for this operation');
        }

        $races = $this->raceRepository->findRacesBySeasonOrderByStartDateAndStartTime($season);

        if (count($races) === 0) {
            return $this->render('raceResultBets/createRace.html.twig');
        }

        // TODO rename hasUserRaceResultBetsForRace to isBettingPossible, return false if user has bet already, or if
        // race start - time now < 5 minutes. Make 5 minutes configurable.
        return $this->render('raceResultBets/list.html.twig', [
            'raceInfos' => array_map(function($race) use ($user) {
                $raceInfo['race'] = $race;
                $raceInfo['hasUserRaceResultBetsForRace'] = $this->hasUserRaceResultBetsForRace($race, $user);
                return $raceInfo;
            }, $races),
            'season' => $season
        ]);
    }

    #[Route('/race-result-bets/{id}', name: 'app_race_result_bets', methods: ['GET', 'POST'])]
    public function edit(Request $request, $id): Response
    {
        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return $this->render('raceResultBets/createSeason.html.twig');
        }

        $season = $activeSeasons[0];
        $race = $this->raceRepository->find($id);

        if (!$race) {
            return throw $this->createNotFoundException('This race does not exist');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
            return throw $this->createAccessDeniedException('Must be logged in for this operation');
        }

        $raceResultBets = $this->getRaceResultBetsForAllActiveDrivers($race, $user);

        if (count($raceResultBets) > 0) {
            // User already bet. We show him the bet overview instead.
            $scoreCalculator = new ScoreCalculationService($this->seasonRepository, $this->userRepository, $season);
            return $this->renderRaceResultBetsDetail($race, $season, $scoreCalculator->getResultsForRace($race));
        }

        // Build form.
        $formBuilder = $this->generateRaceResultBetsFormBuilder($raceResultBets);
        $formBuilder->setAction($this->generateUrl('app_race_result_bets', ['id' => $id]));
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Validate number of bets.
            $formData = $form->getData();
            $raceResultBetsData = array_filter($formData, function($raceResultBetPosition) {
                return $raceResultBetPosition !== null;
            });

            $noOfBetsRequired = RaceResultBetsController::NO_OF_BETS_PER_USER_PER_RACE;
            if (count($raceResultBetsData) !== $noOfBetsRequired) {
                $error = new FormError('Es müssen genau ' . $noOfBetsRequired . ' Tipps abgegeben werden.');
                $form->addError($error);
            }

            // Validate that the drivers exist.
            $raceResultBetsWithDrivers = [];
            foreach ($raceResultBetsData as $driverId => $position) {
                $driver = $this->driverRepository->find($driverId);

                if ($driver) {
                    $raceResultBetsWithDrivers[] = [
                        'driver' => $driver,
                        'position' => $position
                    ];
                } else {
                    // This shouldnt happen.
                    $error = new FormError('Der Fahrer existiert nicht. Bitte versuche es erneut');
                    $form->addError($error);
                }
            }

            if ($form->isValid()) {
                // Remove the old race results.
                $this->removeOldRaceResults($race, $user);

                foreach ($raceResultBetsWithDrivers as $raceResultBetData) {
                    $raceResultBet = new RaceResultBet();
                    $raceResultBet->setRace($race);
                    $raceResultBet->setUser($user);
                    $raceResultBet->setDriver($raceResultBetData['driver']);
                    $raceResultBet->setPosition($raceResultBetData['position']);
                    $this->entityManager->persist($raceResultBet);
                }

                $this->entityManager->flush();
                $this->addFlash(ToastDto::FLASH_TYPE, ToastFactory::generateSaveSuccessfulToast());

                return $this->redirectToRoute('app_race_result_bets_list');
            }
        }

        return $this->render('raceResultBets/edit.html.twig', [
            'form' => $form,
            'raceResultBets' => $raceResultBets,
            'race' => $race,
            'season' => $season
        ]);
    }

    /**
     * @param Race $race
     * @return Response
     */
    private function renderRaceResultBetsDetail(Race $race, Season $season, ResultsForRace $resultsForRace): Response {
        return $this->render('raceResultBets/detail.html.twig', [
            'resultsForRace' => $resultsForRace,
            'race' => $race,
            'season' => $season
        ]);
    }

    /**
     * @param Race $race
     * @param User $user
     */
    private function removeOldRaceResults(Race $race, User $user): void
    {
        // Find bets for the race of the currently logged in user if bets exists.
        $raceResultBets = $this->raceResultBetRepository->findRaceResultBetssByRaceAndUser($race, $user);
        
        foreach ($raceResultBets as $raceResultBet) {
            $this->entityManager->remove($raceResultBet);
        }

        $this->entityManager->flush();
    }

    /**
     * @param Race $race
     * @param User $user
     * @return RaceResultBet[]
     */
    private function getRaceResultBetsForAllActiveDrivers(Race $race, User $user): array
    {
        $drivers = $this->driverRepository->findActiveDrivers();
        $raceResultBets = [];

        foreach ($drivers as $driver) {
            $raceResultBet = new RaceResultBet();
            $raceResultBet->setRace($race);
            $raceResultBet->setUser($user);
            $raceResultBet->setDriver($driver);
            $raceResultBets[] = $raceResultBet;
        }

        return $raceResultBets;
    }


    private function hasUserRaceResultBetsForRace(Race $race, User $user): bool {
        // Find bets for the race of the currently logged in user if bets exists.
        $raceResultBets = $this->raceResultBetRepository->findRaceResultBetssByRaceAndUser($race, $user);

        return count($raceResultBets) > 0;
    }

    /**
     * @param RaceResultBet[] $raceResultBets
     * @return FormBuilderInterface
     */
    private function generateRaceResultBetsFormBuilder($raceResultBets): FormBuilderInterface
    {
        $formBuilder = $this->createFormBuilder();

        foreach ($raceResultBets as $raceResultBet) {
            // Add position number field.
            $options = [
                'required' => false,
                'scale' => 0,
                'data' => null,
                'empty_data' => null,
                'attr' => [
                    'min' => 0
                ]
            ];

            $formBuilder->add($raceResultBet->getDriver()->getId(), NumberType::class, $options);
        }

        $formBuilder->add('submit', SubmitType::class, [
            'label' => 'Speichern'
        ]);

        return $formBuilder;
    }
}
