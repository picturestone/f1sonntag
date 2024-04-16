<?php

namespace App\Controller;

use App\Entity\Season;
use App\Entity\User;
use App\Form\SeasonActiveType;
use App\Form\SeasonType;
use App\Repository\SeasonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted(User::ROLE_ADMIN)]
class SeasonsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SeasonRepository $seasonRepository
    ) {
    }

    #[Route('/seasons', name: 'app_seasons_list', methods: ['GET'])]
    public function list(): Response
    {
        $seasons = $this->seasonRepository->findAll();

        return $this->render('admin/seasons/list.html.twig', [
            'seasons' => $seasons
        ]);
    }

    #[Route('/seasons/new', name: 'app_seasons_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $season = new Season();
        $form = $this->createForm(SeasonType::class, $season, [
            'action' => $this->generateUrl('app_seasons_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save new season entity.
            $season = $form->getData();
            $this->entityManager->persist($season);
            $this->entityManager->flush();

            // Set newly created season to active.
            $this->setSeasonToActive($season->getId());
            $this->entityManager->flush();

            return $this->redirectToRoute('app_seasons_list');
        }

        return $this->render('admin/seasons/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/seasons/edit-active-season', name: 'app_seasons_edit_active_season', methods: ['GET', 'POST'])]
    public function editActiveSeason(Request $request): Response
    {
        $form = $this->createForm(SeasonActiveType::class, null, [
            'action' => $this->generateUrl('app_seasons_edit_active_season'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formValues = $request->request->all();
            $id = $formValues['season_active']['activeSeasonId'];
            $this->setSeasonToActive($id);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_seasons_list');
        }

        return $this->render('admin/seasons/editActiveSeason.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/seasons/edit/{id}', name: 'app_seasons_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, $id): Response
    {
        $season = $this->seasonRepository->find($id);
        if (!$season) {
            return throw $this->createNotFoundException('This season does not exist');
        }
        $form = $this->createForm(SeasonType::class, $season, [
            'action' => $this->generateUrl('app_seasons_edit', ['id' => $id]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_seasons_list');
        }

        return $this->render('admin/seasons/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/seasons/delete/{id}', name: 'app_seasons_delete', methods: ['GET'])]
    public function delete(Request $request, $id): Response
    {
        $season = $this->seasonRepository->find($id);

        if (!$season) {
            return throw $this->createNotFoundException('This season does not exist');
        }

        if ($season->isActive()) {
            return throw new BadRequestHttpException('Active season cannot be deleted');
        }

        $this->entityManager->remove($season);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_seasons_list');
    }

    /**
     * Sets the season with $id to active and all others to inactive.
     *
     * @param int $id
     * @return void
     */
    private function setSeasonToActive(int $id): void {
        $seasons = $this->seasonRepository->findAll();

        foreach ($seasons as $season) {
            $season->setIsActive($season->getId() === $id);
        }
    }
}
