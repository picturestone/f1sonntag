<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\User;
use App\Form\TeamType;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted(User::ROLE_ADMIN)]
class TeamsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TeamRepository $teamRepository
    ) {
    }

    #[Route('/teams', name: 'app_admin_teams_list', methods: ['GET'])]
    public function list(): Response
    {
        $teams = $this->teamRepository->findAll();

        return $this->render('admin/teams/list.html.twig', [
            'teams' => $teams
        ]);
    }

    #[Route('/teams/new', name: 'app_admin_teams_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team, [
            'action' => $this->generateUrl('app_admin_teams_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $team = $form->getData();
            $this->entityManager->persist($team);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_teams_list');
        }

        return $this->render('admin/teams/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/teams/edit/{id}', name: 'app_admin_teams_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, $id): Response
    {
        $team = $this->teamRepository->find($id);
        if (!$team) {
            return throw $this->createNotFoundException('This team does not exist');
        }
        $form = $this->createForm(TeamType::class, $team, [
            'action' => $this->generateUrl('app_admin_teams_edit', ['id' => $id]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_teams_list');
        }

        return $this->render('admin/teams/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/teams/delete/{id}', name: 'app_admin_teams_delete', methods: ['GET'])]
    public function delete(Request $request, $id): Response
    {
        $team = $this->teamRepository->find($id);

        if (!$team) {
            return throw $this->createNotFoundException('This team does not exist');

        }

        $this->entityManager->remove($team);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_admin_teams_list');
    }
}
