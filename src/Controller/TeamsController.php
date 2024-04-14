<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TeamsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/teams', name: 'app_teams', methods: ['GET', 'POST'])]
    public function index(): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);

        if ($form->isSubmitted() && $form->isValid()) {
            $team = $form->getData();
            $this->entityManager->persist($team);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_teams');
        }

        return $this->render('teams/index.html.twig', [
            'form' => $form,
        ]);
    }
}
