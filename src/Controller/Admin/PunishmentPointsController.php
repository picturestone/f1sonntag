<?php

namespace App\Controller\Admin;

use App\Dto\ToastDto;
use App\Entity\PunishmentPoints;
use App\Entity\Race;
use App\Entity\RaceResult;
use App\Entity\User;
use App\Repository\DriverRepository;
use App\Repository\PunishmentPointsRepository;
use App\Repository\RaceRepository;
use App\Repository\SeasonRepository;
use App\Repository\UserRepository;
use App\Service\ToastFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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

        $racePunishmentPoints = $this->getPunishmentPoints($race);

        // Build form.
        $formBuilder = $this->generatePunishmentPointsEditFormBuilder($racePunishmentPoints);
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
            $this->addFlash(ToastDto::FLASH_TYPE, ToastFactory::generateSaveSuccessfulToast());

            return $this->redirectToRoute('app_admin_punishment_points_list');
        }

        return $this->render('admin/punishmentPoints/edit.html.twig', [
            'form' => $form,
            'racePunishmentPoints' => $racePunishmentPoints,
            'race' => $race,
            'season' => $season
        ]);
    }

    #[Route('/punishment-points/{id}/entries', name: 'app_admin_punishment_points_entries', methods: ['GET', 'POST'])]
    public function entryList(Request $request, $id): Response
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

        $racePunishmentPoints = $this->getPunishmentPoints($race);
        $entries = $this->getEntries($racePunishmentPoints);

        // Build form.
        $formBuilder = $this->generatePunishmentPointsEntriesFormBuilder($entries);
        $formBuilder->setAction($this->generateUrl('app_admin_punishment_points_entries', ['id' => $id]));
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            foreach ($formData as $userId => $isEntry) {
                $userPunishmentPoints = array_filter($racePunishmentPoints, function(PunishmentPoints $punishmentPoints) use ($userId) {
                    return $punishmentPoints->getUser()->getId() === $userId;
                });

                if ($isEntry && count($userPunishmentPoints) === 0) {
                    // An entry for this user should exist, but non exist right now. Create one.
                    $punishmentPoints = new PunishmentPoints();
                    $punishmentPoints->setRace($race);
                    $punishmentPoints->setUser($entries[$userId]['user']);
                    $this->entityManager->persist($punishmentPoints);
                } else if (!$isEntry && count($userPunishmentPoints) > 0) {
                    // An entry for this user exists, even though it should not. Delete it.
                    foreach($userPunishmentPoints as $resultToDelete) {
                        $this->entityManager->remove($resultToDelete);
                    }
                } else if ($isEntry && count($userPunishmentPoints) > 0) {
                    // An entry for this user should exist and we have one. However, if the admin clicked on the edit
                    // entries button without saving any entries first, the entries we show are the default ones from
                    // active users which are not persisted yet. We must make sure to persist those entries.
                    foreach($userPunishmentPoints as $resultToPersist) {
                        $this->entityManager->persist($resultToPersist);
                    }
                }
            }

            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_punishment_points', ['id' => $id]);
        }

        return $this->render('admin/punishmentPoints/entries.html.twig', [
            'form' => $form,
            'entries' => $entries,
            'race' => $race,
            'season' => $season
        ]);
    }

    /**
     * @param Race $race
     * @return PunishmentPoints[]
     */
    private function getPunishmentPoints(Race $race): array
    {
        // Find punishment points for the race if punishment points exists.
        $racePunishmentPoints = $this->punishmentPointsRepository->findPunihsmentPointsByRace($race);

        // If no punishment points have been entered yet, add punishment points for all active users as default.
        if (count($racePunishmentPoints) === 0) {
            $activeUsers = $this->userRepository->findActiveUsers();

            foreach ($activeUsers as $activeUser) {
                $punishmentPoints = new PunishmentPoints();
                $punishmentPoints->setRace($race);
                $punishmentPoints->setUser($activeUser);
                $racePunishmentPoints[] = $punishmentPoints;
            }
        }

        return $racePunishmentPoints;
    }

    /**
     * @param PunishmentPoints[] $racePunishmentPoints
     * @return array
     */
    private function getEntries(array $racePunishmentPoints): array
    {
        $users = $this->userRepository->findAll();
        $entries = [];

        foreach ($users as $user) {
            $userPunishmentPoints = array_filter($racePunishmentPoints, function(PunishmentPoints $punishmentPoints) use ($user) {
                return $punishmentPoints->getUser()->getId() === $user->getId();
            });
            $entry = [
                'user' => $user,
                'isEntry' => count($userPunishmentPoints) > 0
            ];
            $entries[$user->getId()] = $entry;
        }

        return $entries;
    }

    /**
     * @param PunishmentPoints[] $racePunishmentPoints
     * @return FormBuilderInterface
     */
    private function generatePunishmentPointsEditFormBuilder(array $racePunishmentPoints): FormBuilderInterface
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

    /**
     * @param array[] $entries
     * @return FormBuilderInterface
     */
    private function generatePunishmentPointsEntriesFormBuilder(array $entries): FormBuilderInterface
    {
        $formBuilder = $this->createFormBuilder();

        foreach ($entries as $entryKey => $entryValue) {
            $formBuilder->add($entryKey, CheckboxType::class, [
                'required' => false,
                'data' => $entryValue['isEntry'],
                'label' => false
            ]);
        }

        $formBuilder->add('submit', SubmitType::class, [
            'label' => 'Speichern'
        ]);

        return $formBuilder;
    }
}
