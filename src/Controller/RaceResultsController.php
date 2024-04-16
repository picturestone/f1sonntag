<?php

namespace App\Controller;

use App\Entity\RaceResult;
use App\Entity\Team;
use App\Entity\User;
use App\Form\TeamType;
use App\Repository\DriverRepository;
use App\Repository\RaceRepository;
use App\Repository\RaceResultRepository;
use App\Repository\SeasonRepository;
use App\Repository\TeamRepository;
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

    #[Route('/race-results', name: 'app_race_results_list', methods: ['GET'])]
    public function list(): Response
    {
        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return $this->render('admin/racesResults/createSeason.html.twig');
        }

        $season = $activeSeasons[0];
        $races = $this->raceRepository->findRacesBySeasonOrderByStartDateAndStartTime($season);

        return $this->render('admin/raceResults/list.html.twig', [
            'races' => $races,
            'season' => $season
        ]);
    }

    #[Route('/race-results/{id}', name: 'app_races_results', methods: ['GET', 'POST'])]
    public function edit(Request $request, $id): Response
    {
        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return $this->render('admin/racesResults/createSeason.html.twig');
        }

        $season = $activeSeasons[0];
        $race = $this->raceRepository->find($id);

        if (!$race) {
            return throw $this->createNotFoundException('This race does not exist');
        }

        // Find results for the race if results exists.
        $raceResults = $this->raceResultRepository->findRaceResultsByRace($race);

        // If no results have been entered yet, add all the active racers for race results as default.
        if (count($raceResults) === 0) {
            $activeDrivers = $this->driverRepository->findActiveDrivers();

            foreach ($activeDrivers as $activeDriver) {
                $raceResult = new RaceResult();
                $raceResult->setRace($race);
                $raceResult->setDriver($activeDriver);
                $raceResults[] = $raceResult;
            }
        }

        // Build form.
        $formBuilder = $this->generateFormBuilder($raceResults);
        $formBuilder->setAction($this->generateUrl('app_races_results', ['id' => $id]));
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

            return $this->redirectToRoute('app_race_results_list');
        }

        return $this->render('admin/raceResults/edit.html.twig', [
            'form' => $form,
            'raceResults' => $raceResults,
            'race' => $race,
            'season' => $season
        ]);
    }

    /**
     * @param RaceResult[] $raceResults
     * @return FormInterface
     */
    private function generateFormBuilder(array $raceResults): FormBuilderInterface
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
}
