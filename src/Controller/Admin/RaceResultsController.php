<?php

namespace App\Controller\Admin;

use App\Dto\ToastDto;
use App\Entity\Race;
use App\Entity\RaceResult;
use App\Entity\User;
use App\Repository\DriverRepository;
use App\Repository\RaceRepository;
use App\Repository\RaceResultRepository;
use App\Repository\SeasonRepository;
use App\Service\ToastFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted(User::ROLE_ADMIN)]
class RaceResultsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SeasonRepository $seasonRepository,
        private readonly RaceResultRepository $raceResultRepository,
        private readonly RaceRepository $raceRepository,
        private readonly DriverRepository $driverRepository
    ) {
    }

    #[Route('/race-results', name: 'app_admin_race_results_list', methods: ['GET'])]
    public function list(): Response
    {
        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return $this->render('admin/raceResults/createSeason.html.twig');
        }

        $season = $activeSeasons[0];
        $races = $this->raceRepository->findRacesBySeasonOrderByStartDateAndStartTime($season);

        if (count($races) === 0) {
            return $this->render('admin/raceResults/createRace.html.twig');
        }

        return $this->render('admin/raceResults/list.html.twig', [
            'races' => $races,
            'season' => $season
        ]);
    }

    #[Route('/race-results/{id}', name: 'app_admin_race_results', methods: ['GET', 'POST'])]
    public function edit(Request $request, $id): Response
    {
        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return $this->render('admin/raceResults/createSeason.html.twig');
        }

        $season = $activeSeasons[0];
        $race = $this->raceRepository->find($id);

        if (!$race) {
            return throw $this->createNotFoundException('This race does not exist');
        }

        $raceResults = $this->getRaceResults($race);

        // Build form.
        $formBuilder = $this->generateRaceResultsEditFormBuilder($raceResults);
        $formBuilder->setAction($this->generateUrl('app_admin_race_results', ['id' => $id]));
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            foreach ($raceResults as $raceResult) {
                $driverId = $raceResult->getDriver()->getId();
                $position = $formData[$driverId];

                if ($position) {
                    $raceResult->setPosition($position);
                }

                $this->entityManager->persist($raceResult);
            }

            $this->entityManager->flush();
            $this->addFlash(ToastDto::FLASH_TYPE, ToastFactory::generateSaveSuccessfulToast());

            return $this->redirectToRoute('app_admin_race_results_list');
        }

        return $this->render('admin/raceResults/edit.html.twig', [
            'form' => $form,
            'raceResults' => $raceResults,
            'race' => $race,
            'season' => $season
        ]);
    }

    #[Route('/race-results/{id}/entries', name: 'app_admin_race_results_entries', methods: ['GET', 'POST'])]
    public function entryList(Request $request, $id): Response
    {
        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return $this->render('admin/raceResults/createSeason.html.twig');
        }

        $season = $activeSeasons[0];
        $race = $this->raceRepository->find($id);

        if (!$race) {
            return throw $this->createNotFoundException('This race does not exist');
        }

        $raceResults = $this->getRaceResults($race);
        $entries = $this->getEntries($raceResults);

        // Build form.
        $formBuilder = $this->generateRaceResultsEntriesFormBuilder($entries);
        $formBuilder->setAction($this->generateUrl('app_admin_race_results_entries', ['id' => $id]));
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            foreach ($formData as $driverId => $isEntry) {
                $driverRaceResults = array_filter($raceResults, function(RaceResult $raceResult) use ($driverId) {
                    return $raceResult->getDriver()->getId() === $driverId;
                });

                if ($isEntry && count($driverRaceResults) === 0) {
                    // An entry for this driver should exist, but non exist right now. Create one.
                    $raceResult = new RaceResult();
                    $raceResult->setRace($race);
                    $raceResult->setDriver($entries[$driverId]['driver']);
                    $this->entityManager->persist($raceResult);
                } else if (!$isEntry && count($driverRaceResults) > 0) {
                    // An entry for this driver exists, even though it should not. Delete it.
                    foreach($driverRaceResults as $resultToDelete) {
                        $this->entityManager->remove($resultToDelete);
                    }
                } else if ($isEntry && count($driverRaceResults) > 0) {
                    // An entry for this driver should exist and we have one. However, if the admin clicked on the edit
                    // entries button without saving any entries first, the entries we show are the default ones from
                    // active drivers which are not persisted yet. We must make sure to persist those entries.
                    foreach($driverRaceResults as $resultToPersist) {
                        $this->entityManager->persist($resultToPersist);
                    }
                }
            }

            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_race_results', ['id' => $id]);
        }

        return $this->render('admin/raceResults/entries.html.twig', [
            'form' => $form,
            'entries' => $entries,
            'race' => $race,
            'season' => $season
        ]);
    }

    /**
     * @param Race $race
     * @return RaceResult[]
     */
    private function getRaceResults(Race $race): array
    {
        // Find results for the race if results exists.
        $raceResults = $this->raceResultRepository->findRaceResultsByRace($race);

        // If no results have been entered yet, add results for all active drivers as default.
        if (count($raceResults) === 0) {
            $activeDrivers = $this->driverRepository->findActiveDrivers();

            foreach ($activeDrivers as $activeDriver) {
                $raceResult = new RaceResult();
                $raceResult->setRace($race);
                $raceResult->setDriver($activeDriver);
                $raceResults[] = $raceResult;
            }
        }

        return $raceResults;
    }

    /**
     * @param RaceResult[] $raceResults
     * @return array
     */
    private function getEntries(array $raceResults): array
    {
        $drivers = $this->driverRepository->findAll();
        $entries = [];

        foreach ($drivers as $driver) {
            $driverRaceResults = array_filter($raceResults, function(RaceResult $raceResult) use ($driver) {
                return $raceResult->getDriver()->getId() === $driver->getId();
            });
            $entry = [
                'driver' => $driver,
                'isEntry' => count($driverRaceResults) > 0
            ];
            $entries[$driver->getId()] = $entry;
        }

        return $entries;
    }

    /**
     * @param RaceResult[] $raceResults
     * @return FormBuilderInterface
     */
    private function generateRaceResultsEditFormBuilder(array $raceResults): FormBuilderInterface
    {
        $formBuilder = $this->createFormBuilder();

        foreach ($raceResults as $raceResult) {
            $options = [
                'scale' => 0,
                'empty_data' => 0,
                'attr' => [
                    'min' => 0
                ]
            ];
            $position = $raceResult->getPosition();

            if ($position) {
                $options['data'] = $position;
            } else {
                $options['data'] = 0;
            }

            $formBuilder->add($raceResult->getDriver()->getId(), NumberType::class, $options);
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
    private function generateRaceResultsEntriesFormBuilder(array $entries): FormBuilderInterface
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
