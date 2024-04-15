<?php

namespace App\Controller;

use App\Entity\Driver;
use App\Entity\User;
use App\Form\DriverType;
use App\Repository\DriverRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted(User::ROLE_ADMIN)]
class DriversController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DriverRepository $driverRepository
    ) {
    }

    #[Route('/drivers', name: 'app_drivers_list', methods: ['GET'])]
    public function listActive(): Response
    {
        $drivers = $this->driverRepository->findAllOrderByIsActiveAndLastName();

        return $this->render('drivers/list.html.twig', [
            'drivers' => $drivers
        ]);
    }

    #[Route('/drivers/new', name: 'app_drivers_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $driver = new Driver();
        $form = $this->createForm(DriverType::class, $driver, [
            'action' => $this->generateUrl('app_drivers_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $driver = $form->getData();
            $this->entityManager->persist($driver);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_drivers_list');
        }

        return $this->render('drivers/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/drivers/edit/{id}', name: 'app_drivers_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, $id): Response
    {
        $driver = $this->driverRepository->find($id);
        if (!$driver) {
            return throw $this->createNotFoundException('This driver does not exist');
        }
        $form = $this->createForm(DriverType::class, $driver, [
            'action' => $this->generateUrl('app_drivers_edit', ['id' => $id]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_drivers_list');
        }

        return $this->render('drivers/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/drivers/delete/{id}', name: 'app_drivers_delete', methods: ['GET'])]
    public function delete(Request $request, $id): Response
    {
        $driver = $this->driverRepository->find($id);

        if (!$driver) {
            return throw $this->createNotFoundException('This driver does not exist');

        }

        $this->entityManager->remove($driver);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_drivers_list');
    }
}
