<?php

namespace App\Controller\Admin;

use App\Dto\ToastDto;
use App\Entity\PenaltyPointsAward;
use App\Entity\Race;
use App\Entity\RaceResult;
use App\Entity\User;
use App\Repository\DriverRepository;
use App\Repository\PenaltyPointsAwardRepository;
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
class PenaltyPointsAwardsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SeasonRepository $seasonRepository,
        private readonly PenaltyPointsAwardRepository $penaltyPointsAwardRepository,
        private readonly RaceRepository $raceRepository,
        private readonly UserRepository $userRepository
    ) {
    }

    #[Route('/penalty-points-awards', name: 'app_admin_penalty_points_awards_list', methods: ['GET'])]
    public function list(): Response
    {
        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return $this->render('admin/penaltyPointsAward/createSeason.html.twig');
        }

        $season = $activeSeasons[0];
        $races = $this->raceRepository->findRacesBySeasonOrderByStartDateAndStartTime($season);

        return $this->render('admin/penaltyPointsAward/list.html.twig', [
            'races' => $races,
            'season' => $season
        ]);
    }

    #[Route('/penalty-points-awards/{id}', name: 'app_admin_penalty_points_awards', methods: ['GET', 'POST'])]
    public function edit(Request $request, $id): Response
    {
        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return $this->render('admin/penaltyPointsAward/createSeason.html.twig');
        }

        $season = $activeSeasons[0];
        $race = $this->raceRepository->find($id);

        if (!$race) {
            return throw $this->createNotFoundException('This race does not exist');
        }

        $penaltyPointsAwards = $this->getPenaltyPointsAward($race);

        // Build form.
        $formBuilder = $this->generatePenaltyPointsAwardEditFormBuilder($penaltyPointsAwards);
        $formBuilder->setAction($this->generateUrl('app_admin_penalty_points_awards', ['id' => $id]));
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            foreach ($penaltyPointsAwards as $penaltyPointsAward) {
                $userId = $penaltyPointsAward->getUser()->getId();
                $penaltyPoints = $formData[$userId];

                if ($penaltyPoints) {
                    $penaltyPointsAward->setPenaltyPoints($penaltyPoints);
                }

                $this->entityManager->persist($penaltyPointsAward);
            }

            $this->entityManager->flush();
            $this->addFlash(ToastDto::FLASH_TYPE, ToastFactory::generateSaveSuccessfulToast());

            return $this->redirectToRoute('app_admin_penalty_points_awards_list');
        }

        return $this->render('admin/penaltyPointsAward/edit.html.twig', [
            'form' => $form,
            'penaltyPointsAwards' => $penaltyPointsAwards,
            'race' => $race,
            'season' => $season
        ]);
    }

    #[Route('/penalty-points-awards/{id}/entries', name: 'app_admin_penalty_points_awards_entries', methods: ['GET', 'POST'])]
    public function entryList(Request $request, $id): Response
    {
        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return $this->render('admin/penaltyPointsAward/createSeason.html.twig');
        }

        $season = $activeSeasons[0];
        $race = $this->raceRepository->find($id);

        if (!$race) {
            return throw $this->createNotFoundException('This race does not exist');
        }

        $penaltyPointsAwards = $this->getPenaltyPointsAward($race);
        $entries = $this->getEntries($penaltyPointsAwards);

        // Build form.
        $formBuilder = $this->generatePenaltyPointsAwardEntriesFormBuilder($entries);
        $formBuilder->setAction($this->generateUrl('app_admin_penalty_points_awards_entries', ['id' => $id]));
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            foreach ($formData as $userId => $isEntry) {
                $userPenaltyPointsAwards = array_filter(
                    $penaltyPointsAwards,
                    function(PenaltyPointsAward $penaltyPointsAward) use ($userId) {
                        return $penaltyPointsAward->getUser()->getId() === $userId;
                });

                if ($isEntry && count($userPenaltyPointsAwards) === 0) {
                    // An entry for this user should exist, but non exist right now. Create one.
                    $penaltyPointsAward = new PenaltyPointsAward();
                    $penaltyPointsAward->setRace($race);
                    $penaltyPointsAward->setUser($entries[$userId]['user']);
                    $this->entityManager->persist($penaltyPointsAward);
                } else if (!$isEntry && count($userPenaltyPointsAwards) > 0) {
                    // An entry for this user exists, even though it should not. Delete it.
                    foreach($userPenaltyPointsAwards as $resultToDelete) {
                        $this->entityManager->remove($resultToDelete);
                    }
                } else if ($isEntry && count($userPenaltyPointsAwards) > 0) {
                    // An entry for this user should exist and we have one. However, if the admin clicked on the edit
                    // entries button without saving any entries first, the entries we show are the default ones from
                    // active users which are not persisted yet. We must make sure to persist those entries.
                    foreach($userPenaltyPointsAwards as $resultToPersist) {
                        $this->entityManager->persist($resultToPersist);
                    }
                }
            }

            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_penalty_points_awards', ['id' => $id]);
        }

        return $this->render('admin/penaltyPointsAward/entries.html.twig', [
            'form' => $form,
            'entries' => $entries,
            'race' => $race,
            'season' => $season
        ]);
    }

    /**
     * @param Race $race
     * @return PenaltyPointsAward[]
     */
    private function getPenaltyPointsAward(Race $race): array
    {
        // Find penalty points awards for the race if penalty points awards exists.
        $penaltyPointsAwards = $this->penaltyPointsAwardRepository->findPenaltyPointsAwardsByRace($race);

        // If no penalty points awards have been entered yet, add penalty points awards for all active users as default.
        if (count($penaltyPointsAwards) === 0) {
            $activeUsers = $this->userRepository->findActiveUsers();

            foreach ($activeUsers as $activeUser) {
                $penaltyPointsAward = new PenaltyPointsAward();
                $penaltyPointsAward->setRace($race);
                $penaltyPointsAward->setUser($activeUser);
                $penaltyPointsAwards[] = $penaltyPointsAward;
            }
        }

        return $penaltyPointsAwards;
    }

    /**
     * @param PenaltyPointsAward[] $penaltyPointsAwards
     * @return array
     */
    private function getEntries(array $penaltyPointsAwards): array
    {
        $users = $this->userRepository->findAll();
        $entries = [];

        foreach ($users as $user) {
            $userPenaltyPointsAwards = array_filter(
                $penaltyPointsAwards,
                function(PenaltyPointsAward $penaltyPointsAward) use ($user) {
                    return $penaltyPointsAward->getUser()->getId() === $user->getId();
            });
            $entry = [
                'user' => $user,
                'isEntry' => count($userPenaltyPointsAwards) > 0
            ];
            $entries[$user->getId()] = $entry;
        }

        return $entries;
    }

    /**
     * @param PenaltyPointsAward[] $penaltyPointsAwards
     * @return FormBuilderInterface
     */
    private function generatePenaltyPointsAwardEditFormBuilder(array $penaltyPointsAwards): FormBuilderInterface
    {
        $formBuilder = $this->createFormBuilder();

        foreach ($penaltyPointsAwards as $penaltyPointsAward) {
            $options = [
                'scale' => 0,
                'empty_data' => 0,
                'attr' => [
                    'min' => 0
                ]
            ];
            $points = $penaltyPointsAward->getPenaltyPoints();

            if ($points) {
                $options['data'] = $points;
            } else {
                $options['data'] = 0;
            }

            $formBuilder->add($penaltyPointsAward->getUser()->getId(), NumberType::class, $options);
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
    private function generatePenaltyPointsAwardEntriesFormBuilder(array $entries): FormBuilderInterface
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
