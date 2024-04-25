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
use App\Repository\SeasonRepository;
use App\Repository\WorldChampionBetRepository;
use App\Service\ToastFactory;
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
        private readonly WorldChampionBetRepository $worldChampionBetRepository
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

        $worldChampionBets = $this->worldChampionBetRepository->findWorldChampionBetsBySeason($season);

        return $this->render('worldChampionBets/list.html.twig', [
            'worldChampionBets' => $worldChampionBets,
            'season' => $season
        ]);
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

            return $this->redirectToRoute('app_world_champion_bets_list');
        }

        return $this->render('worldChampionBets/edit.html.twig', [
            'form' => $form,
        ]);
    }

    // TODO implement.
    private function isBettingPossible(Race $race, User $user): bool {
        $isBettingPossible = true;

        // Find bets for the race of the currently logged in user if bets exists. If the user already has bets they
        // can no longer bet.
        $raceResultBets = $this->raceResultBetRepository->findRaceResultBetssByRaceAndUser($race, $user);
        if (count($raceResultBets) > 0) {
            $isBettingPossible = false;
        }

        // TODO if race start - time now < 5 minutes. Make 5 minutes configurable.

        return $isBettingPossible;
    }
}
