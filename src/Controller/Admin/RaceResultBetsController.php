<?php

namespace App\Controller\Admin;

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
use App\Service\ToastFactory;
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

#[Route('/admin')]
#[IsGranted(User::ROLE_BETS_EDIT)]
class RaceResultBetsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface  $entityManager,
        private readonly SeasonRepository $seasonRepository,
        private readonly UserRepository $userRepository,
        private readonly RaceResultBetRepository $raceResultBetRepository,
        private readonly RaceRepository $raceRepository,
        private readonly DriverRepository $driverRepository
    ) {
    }

    #[Route('/race-result-bets', name: 'app_admin_race_result_bets_races', methods: ['GET'])]
    public function races(): Response
    {
        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return $this->render('admin/raceResultBets/createSeason.html.twig');
        }

        $season = $activeSeasons[0];

        $races = $this->raceRepository->findRacesBySeasonOrderByStartDateTime($season);

        if (count($races) === 0) {
            return $this->render('admin/raceResultBets/createRace.html.twig');
        }

        return $this->render('admin/raceResultBets/races.html.twig', [
            'races' => $races,
            'season' => $season
        ]);
    }

    #[Route('/race-result-bets/{raceId}', name: 'app_admin_race_result_bets_users', methods: ['GET'])]
    public function users(Request $request, $raceId): Response
    {
        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return $this->render('admin/raceResultBets/createSeason.html.twig');
        }

        $season = $activeSeasons[0];
        $race = $this->raceRepository->find($raceId);

        if (!$race) {
            return throw $this->createNotFoundException('This race does not exist');
        }

        $users = $this->userRepository->findAll();

        if (count($users) === 0) {
            return $this->render('admin/raceResultBets/createUser.html.twig');
        }

        return $this->render('admin/raceResultBets/users.html.twig', [
            'users' => $users,
            'season' => $season,
            'race' => $race
        ]);
    }

    #[Route('/race-result-bets/{raceId}/{userId}', name: 'app_admin_race_result_bets_detail', methods: ['GET'])]
    public function detail(Request $request, $raceId, $userId): Response
    {
        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return $this->render('admin/raceResultBets/createSeason.html.twig');
        }

        $season = $activeSeasons[0];
        $race = $this->raceRepository->find($raceId);

        if (!$race) {
            return throw $this->createNotFoundException('This race does not exist');
        }

        $user = $this->userRepository->find($userId);

        if (!$user) {
            return throw $this->createNotFoundException('This user does not exist');
        }

        $raceResultBets = $this->raceResultBetRepository->findRaceResultBetsByRaceAndUser($race, $user);

        return $this->render('admin/raceResultBets/detail.html.twig', [
            'raceResultBets' => $raceResultBets,
            'race' => $race,
            'user' => $user,
            'season' => $season
        ]);
    }

    #[Route('/race-result-bets/{raceId}/{userId}/delete', name: 'app_admin_race_result_bets_delete', methods: ['GET'])]
    public function delete(Request $request, $raceId, $userId): Response
    {
        $race = $this->raceRepository->find($raceId);

        if (!$race) {
            return throw $this->createNotFoundException('This race does not exist');
        }

        $user = $this->userRepository->find($userId);

        if (!$user) {
            return throw $this->createNotFoundException('This user does not exist');
        }

        $this->removeRaceResultBets($race, $user);
        $this->addFlash(ToastDto::FLASH_TYPE, ToastFactory::generateDeleteSuccessfulToast());

        return $this->redirectToRoute('app_admin_race_result_bets_races');
    }

    #[Route('/race-result-bets/{raceId}/{userId}/edit', name: 'app_admin_race_result_bets_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, $raceId, $userId): Response
    {
        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return $this->render('admin/raceResultBets/createSeason.html.twig');
        }

        $season = $activeSeasons[0];
        $race = $this->raceRepository->find($raceId);

        if (!$race) {
            return throw $this->createNotFoundException('This race does not exist');
        }

        $user = $this->userRepository->find($userId);

        if (!$user) {
            return throw $this->createNotFoundException('This user does not exist');
        }

        // Build form.
        $raceResultBets = $this->getRaceResultBetsForAllActiveDrivers($race, $user);
        $formBuilder = $this->generateRaceResultBetsFormBuilder($raceResultBets);
        $formBuilder->setAction($this->generateUrl('app_admin_race_result_bets_edit', ['raceId' => $raceId, 'userId' => $userId]));
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            return $this->renderFormSubmission($race, $season, $user, $raceResultBets, $form);
        } else {
            return $this->renderRaceResultBetsEditForm($race, $season, $user, $raceResultBets, $form);
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

        $noOfBetsRequired = intval($this->getParameter('F1SONNTAG_NUMBER_OF_BETS_PER_USER_PER_RACE'));

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
            $this->removeRaceResultBets($race, $user);

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

            return $this->redirectToRoute('app_admin_race_result_bets_races');
        } else {
            return $this->renderRaceResultBetsEditForm($race, $season, $user, $raceResultBets, $form);
        }
    }

    /**
     */
    private function renderRaceResultBetsEditForm(
        Race $race,
        Season $season,
        User $user,
        array $raceResultBets,
        FormInterface $form
    ): Response {
        return $this->render('admin/raceResultBets/edit.html.twig', [
            'form' => $form,
            'raceResultBets' => $raceResultBets,
            'race' => $race,
            'season' => $season,
            'user' => $user
        ]);
    }

    /**
     * @param Race $race
     * @param User $user
     */
    private function removeRaceResultBets(Race $race, User $user): void
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
