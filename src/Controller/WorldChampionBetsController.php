<?php

namespace App\Controller;

use App\Dto\ToastDto;
use App\Entity\Season;
use App\Entity\User;
use App\Entity\WorldChampionBet;
use App\Form\Admin\SeasonActiveType;
use App\Form\Admin\SeasonType;
use App\Form\Admin\WorldChampionBetType;
use App\Repository\DriverRepository;
use App\Repository\RaceRepository;
use App\Repository\SeasonRepository;
use App\Repository\WorldChampionBetRepository;
use App\Service\ToastFactory;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_USER)]
class WorldChampionBetsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SeasonRepository $seasonRepository,
        private readonly DriverRepository $driverRepository,
        private readonly WorldChampionBetRepository $worldChampionBetRepository,
        private readonly RaceRepository $raceRepository,
    ) {
    }

    #[Route('/world-champion-bets', name: 'app_world_champion_bets_list', methods: ['GET'])]
    public function list(): Response
    {
        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return $this->render('worldChampionBets/createSeason.html.twig');
        }

        $season = $activeSeasons[0];

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
            return throw $this->createAccessDeniedException('Must be logged in for this operation');
        }

        $firstRace = $this->raceRepository->findFirstRaceOfSeason($season);
        if ($firstRace === null) {
            return $this->render('worldChampionBets/createRace.html.twig');
        }

        $isTimePastBettingLimit = $this->isTimePastBettingLimit($season);

        if ($isTimePastBettingLimit) {
            $worldChampionBets = $this->worldChampionBetRepository->findWorldChampionBetsBySeason($season);

            return $this->render('worldChampionBets/list.html.twig', [
                'worldChampionBets' => $worldChampionBets,
                'season' => $season
            ]);
        } else {
            $worldChampionBet = $this->worldChampionBetRepository->findOneBy([
                'user' => $user,
                'season' => $season
            ]);
            return $this->render('worldChampionBets/betOfUser.html.twig', [
                'worldChampionBet' => $worldChampionBet,
                'season' => $season,
                'firstRace' => $firstRace
            ]);
        }
    }

    #[Route('/world-champion-bets/edit', name: 'app_world_champion_bets_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request): Response
    {
        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return $this->render('worldChampionBets/createSeason.html.twig');
        }

        $season = $activeSeasons[0];

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
            return throw $this->createAccessDeniedException('Must be logged in for this operation');
        }

        $worldChampionBet = $this->worldChampionBetRepository->findOneBy([
            'season' => $season,
            'user' => $user
        ]);

        $data = null;
        if ($worldChampionBet !== null) {
            $data = [
                'driverId' => $worldChampionBet->getDriver()
            ];
        }

        $form = $this->createForm(WorldChampionBetType::class, $data, [
            'action' => $this->generateUrl('app_world_champion_bets_edit'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->isTimePastBettingLimit($season)) {
                // The user submitted the championship bet too late. show an error toast and go back to the list.
                $errorMessage = 'Das Zeitfenster zum Abgeben eines Weltmeister-Tipps ist leider abgelaufen.';

                $this->addFlash(ToastDto::FLASH_TYPE, ToastFactory::generateCustomErrorToast($errorMessage));
            } else {
                $formValues = $request->request->all();
                $driverId = $formValues['world_champion_bet']['driverId'];

                $driver = $this->driverRepository->find($driverId);
                if (!$driver) {
                    return throw $this->createNotFoundException('This driver does not exist');
                }

                if ($worldChampionBet) {
                    $worldChampionBet->setDriver($driver);
                } else {
                    $worldChampionBet = new WorldChampionBet();
                    $worldChampionBet->setDriver($driver);
                    $worldChampionBet->setUser($user);
                    $worldChampionBet->setSeason($season);
                }

                $this->entityManager->persist($worldChampionBet);
                $this->entityManager->flush();
                $this->addFlash(ToastDto::FLASH_TYPE, ToastFactory::generateSaveSuccessfulToast());
            }

            return $this->redirectToRoute('app_world_champion_bets_list');
        }

        return $this->render('worldChampionBets/edit.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Checks if the configured betting time limit allows betting on the world championship at the time of calling this
     * function.
     *
     * @param Season $season
     * @return bool
     * @throws \Exception
     */
    private function isTimePastBettingLimit(Season $season): bool {
        $isTimePastBettingLimit = false;

        // If the race start is less than x minutes away, betting cannot take place anymore.
        $now = new \DateTimeImmutable('now', new DateTimeZone('UTC'));
        $firstRace = $this->raceRepository->findFirstRaceOfSeason($season);

        if ($now > $firstRace->getStartDateTime()) {
            $isTimePastBettingLimit = true;
        }

        // TODO for testing only.
        return false;

        return $isTimePastBettingLimit;
    }
}
