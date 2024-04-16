<?php

namespace App\Controller\Admin;

use App\Entity\PunishmentPoints;
use App\Entity\RaceResult;
use App\Entity\User;
use App\Repository\DriverRepository;
use App\Repository\PunishmentPointsRepository;
use App\Repository\RaceRepository;
use App\Repository\SeasonRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted(User::ROLE_ADMIN)]
class PunishmentPointsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SeasonRepository $seasonRepository,
        private readonly PunishmentPointsRepository $punishmentPointsRepository,
        private readonly RaceRepository $raceRepository,
        private readonly UserRepository $userRepository
    ) {
    }

    #[Route('/punishment-points', name: 'app_admin_punishment_points_list', methods: ['GET'])]
    public function list(): Response
    {
        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return $this->render('admin/punishmentPoints/createSeason.html.twig');
        }

        $season = $activeSeasons[0];
        $races = $this->raceRepository->findRacesBySeasonOrderByStartDateAndStartTime($season);

        return $this->render('admin/punishmentPoints/list.html.twig', [
            'races' => $races,
            'season' => $season
        ]);
    }

    #[Route('/punishment-points/{id}', name: 'app_admin_punishment_points', methods: ['GET', 'POST'])]
    public function edit(Request $request, $id): Response
    {
        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return $this->render('admin/punishmentPoints/createSeason.html.twig');
        }

        $season = $activeSeasons[0];
        $race = $this->raceRepository->find($id);

        if (!$race) {
            return throw $this->createNotFoundException('This race does not exist');
        }

        // Find punishment points for the race if punishment points exists.
        $racePunishmentPoints = $this->punishmentPointsRepository->findPunihsmentPointsByRace($race);

        // If no punishment points have been entered yet for the race, add punishment points for all users as default.
        if (count($racePunishmentPoints) === 0) {
            $users = $this->userRepository->findAll();

            foreach ($users as $user) {
                $punishmentPoints = new PunishmentPoints();
                $punishmentPoints->setRace($race);
                $punishmentPoints->setUser($user);
                $racePunishmentPoints[] = $punishmentPoints;
            }
        }

        // TODO either make users inactive or add punishmentPoints for users which dont have them yet for the race.
        // Pretty much do the same as for raceResults.

        // Build form.
        $formBuilder = $this->generateFormBuilder($racePunishmentPoints);
        $formBuilder->setAction($this->generateUrl('app_admin_punishment_points', ['id' => $id]));
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            foreach ($racePunishmentPoints as $punishmentPoints) {
                $userId = $punishmentPoints->getUser()->getId();
                $points = $formData[$userId];

                if ($points) {
                    $punishmentPoints->setPunishmentPoints($points);
                }

                $this->entityManager->persist($punishmentPoints);
            }

            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_punishment_points_list');
        }

        return $this->render('admin/punishmentPoints/edit.html.twig', [
            'form' => $form,
            'racePunishmentPoints' => $racePunishmentPoints,
            'race' => $race,
            'season' => $season
        ]);
    }

    /**
     * @param PunishmentPoints[] $racePunishmentPoints
     * @return FormInterface
     */
    private function generateFormBuilder(array $racePunishmentPoints): FormBuilderInterface
    {
        $formBuilder = $this->createFormBuilder();

        foreach ($racePunishmentPoints as $punishmentPoints) {
            $options = [
                'scale' => 0,
                'empty_data' => 0,
                'attr' => [
                    'min' => 0
                ]
            ];
            $points = $punishmentPoints->getPunishmentPoints();

            if ($points) {
                $options['data'] = $points;
            } else {
                $options['data'] = 0;
            }

            $formBuilder->add($punishmentPoints->getUser()->getId(), NumberType::class, $options);
        }

        $formBuilder->add('submit', SubmitType::class, [
            'label' => 'Speichern'
        ]);

        return $formBuilder;
    }
}
