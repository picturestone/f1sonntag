<?php

namespace App\Controller\Admin;

use App\Dto\ToastDto;
use App\Entity\Race;
use App\Entity\User;
use App\Form\Admin\RaceType;
use App\Repository\RaceRepository;
use App\Repository\SeasonRepository;
use App\Service\ToastFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted(User::ROLE_ADMIN)]
class RacesController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RaceRepository $raceRepository,
        private readonly SeasonRepository $seasonRepository
    ) {
    }

    #[Route('/races', name: 'app_admin_races_list', methods: ['GET'])]
    public function list(): Response
    {
        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return $this->render('admin/races/createSeason.html.twig');
        }

        $season = $activeSeasons[0];
        $races = $this->raceRepository->findRacesBySeasonOrderByStartDateAndStartTime($season);

        return $this->render('admin/races/list.html.twig', [
            'races' => $races,
            'season' => $season
        ]);
    }

    #[Route('/races/new', name: 'app_admin_races_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return throw new BadRequestHttpException('A season must be active.');
        }

        $activeSeason = $activeSeasons[0];
        $race = new Race();
        $form = $this->createForm(RaceType::class, $race, [
            'action' => $this->generateUrl('app_admin_races_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Race $race */
            $race = $form->getData();
            $race->setSeason($activeSeason);
            $this->entityManager->persist($race);
            $this->entityManager->flush();
            $this->addFlash(ToastDto::FLASH_TYPE, ToastFactory::generateSaveSuccessfulToast());

            return $this->redirectToRoute('app_admin_races_list');
        }

        return $this->render('admin/races/new.html.twig', [
            'form' => $form,
            'season' => $activeSeason
        ]);
    }

    #[Route('/races/edit/{id}', name: 'app_admin_races_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, $id): Response
    {
        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);

        if (!$activeSeasons) {
            return throw new BadRequestHttpException('A season must be active.');
        }

        $season = $activeSeasons[0];
        $race = $this->raceRepository->find($id);
        if (!$race) {
            return throw $this->createNotFoundException('This race does not exist');
        }
        $form = $this->createForm(RaceType::class, $race, [
            'action' => $this->generateUrl('app_admin_races_edit', ['id' => $id]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash(ToastDto::FLASH_TYPE, ToastFactory::generateSaveSuccessfulToast());

            return $this->redirectToRoute('app_admin_races_list');
        }

        return $this->render('admin/races/edit.html.twig', [
            'form' => $form,
            'season' => $season
        ]);
    }

    #[Route('/races/delete/{id}', name: 'app_admin_races_delete', methods: ['GET'])]
    public function delete(Request $request, $id): Response
    {
        $race = $this->raceRepository->find($id);

        if (!$race) {
            return throw $this->createNotFoundException('This race does not exist');

        }

        $this->entityManager->remove($race);
        $this->entityManager->flush();
        $this->addFlash(ToastDto::FLASH_TYPE, ToastFactory::generateDeleteSuccessfulToast());

        return $this->redirectToRoute('app_admin_races_list');
    }
}
