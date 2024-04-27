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
use App\ScoreCalculation\ScoreCalculationService;
use App\Service\ToastFactory;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
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

        $races = $this->raceRepository->findRacesBySeasonOrderByStartDateTime($season);

        if (count($races) === 0) {
            return $this->render('raceResultBets/createRace.html.twig');
        }

        return $this->render('raceResultBets/list.html.twig', [
            'raceInfos' => array_map(function($race) use ($user) {
                $raceInfo['race'] = $race;
                $raceInfo['isUserWithBetsForRace'] = $this->isUserWithBetsForRace($race, $user);
                $raceInfo['isTimePastBettingLimit'] = $this->isTimePastBettingLimit($race);
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

        $isUserWithBetsForRace = $this->isUserWithBetsForRace($race, $user);
        $isTimePastBettingLimit = $this->isTimePastBettingLimit($race);

        // First we need to check the form submission - if the form is submitted but the time is past due, we will
        // redirect the person to the list, otherwise we take their bet.
        // Build form.
        $raceResultBets = $this->getRaceResultBetsForAllActiveDrivers($race, $user);
        $formBuilder = $this->generateRaceResultBetsFormBuilder($raceResultBets);
        $formBuilder->setAction($this->generateUrl('app_race_result_bets', ['id' => $id]));
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // If there is a submitted form we want to check if the time is past due. This can happen if the user chills
            // on the form submission page for too long.
            if ($isTimePastBettingLimit) {
                // The user submitted the form past due date - we send them back to the list and show them that the time
                // ran out.
                $errorMessage = 'Das Zeitfenster zum Abgeben von Tipps ist leider abgelaufen.';
                $this->addFlash(ToastDto::FLASH_TYPE, ToastFactory::generateCustomErrorToast($errorMessage));

                return $this->redirectToRoute('app_race_result_bets_list');
            } else {
                // The user submitted the form on time. We handle the form.
                return $this->renderFormSubmission($race, $season, $user, $raceResultBets, $form);
            }
        } else if ($isTimePastBettingLimit) {
            // If the time is past the betting limit we want to show them all the user's bets.
            return $this->renderRaceResultBetsForAllUsers($race, $season);
        } else if ($isUserWithBetsForRace) {
            // If the time is not past the betting limit, but the user has bets, we want to show them the bets they
            // made.
            return $this->renderRaceResultBetsForSingleUsers($race, $season, $user);
        } else {
            // If the time is not past the betting limit, and the user does not have bets yet, we want to show them the
            // form.
            return $this->renderRaceResultBetsEditForm($race, $season, $raceResultBets, $form);
        }
    }

    /**
     *
     */
    private function renderFormSubmission(
        Race $race,
        Season $season,
        User $user,
        array $raceResultBets,
        FormInterface $form
    ): Response {
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
        } else {
            return $this->renderRaceResultBetsEditForm($race, $season, $raceResultBets, $form);
        }
    }

    /**
     * @param Race $race
     * @return Response
     */
    private function renderRaceResultBetsForSingleUsers(Race $race, Season $season, User $user): Response {
        $raceResultBets = $this->raceResultBetRepository->findRaceResultBetsByRaceAndUser($race, $user);

        return $this->render('raceResultBets/betsOfUser.html.twig', [
            'raceResultBets' => $raceResultBets,
            'race' => $race,
            'season' => $season
        ]);
    }

    /**
     * @param Race $race
     * @param Season $season
     * @param array $raceResultBets
     * @param FormInterface $form
     * @return Response
     */
    private function renderRaceResultBetsEditForm(
        Race $race,
        Season $season,
        array $raceResultBets,
        FormInterface $form
    ): Response {
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
    private function renderRaceResultBetsForAllUsers(Race $race, Season $season): Response {
        $scoreCalculator = new ScoreCalculationService($this->seasonRepository, $this->userRepository, $season);
        $resultsForRace = $scoreCalculator->getResultsForRace($race);

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
        $raceResultBets = $this->raceResultBetRepository->findRaceResultBetsByRaceAndUser($race, $user);
        
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

    /**
     * Checks if a user has bets for a race.
     *
     * @param Race $race
     * @param User $user
     * @return bool
     */
    private function isUserWithBetsForRace(Race $race, User $user): bool {
        $isUserWithBetsForRace = false;

        // Find bets for the race of the currently logged in user if bets exists. If the user already has bets they
        // can no longer bet.
        $raceResultBets = $this->raceResultBetRepository->findRaceResultBetsByRaceAndUser($race, $user);
        if (count($raceResultBets) > 0) {
            $isUserWithBetsForRace = true;
        }

        return $isUserWithBetsForRace;
    }

    /**
     * Checks if the configured betting time limit allows betting on this race at the time of calling this function.
     *
     * @param Race $race
     * @param User $user
     * @return bool
     * @throws \Exception
     */
    private function isTimePastBettingLimit(Race $race): bool {
        $isTimePastBettingLimit = false;
        $bettingTimeLimit = $this->getParameter('F1SONNTAG_BETTING_TIME_LIMIT_FROM_RACE_START');

        // If the race start is less than x minutes away, betting cannot take place anymore.
        $now = new \DateTimeImmutable('now', new DateTimeZone('UTC'));

        if ($now > $race->getStartDateTime()->modify($bettingTimeLimit)) {
            $isTimePastBettingLimit = true;
        }

        return $isTimePastBettingLimit;
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
