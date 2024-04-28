<?php

namespace App\Controller;

use App\Dto\ToastDto;
use App\Entity\User;
use App\Service\ToastFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_USER)]
class UsersController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('/users/password', name: 'app_user_users_change_password', methods: ['GET', 'POST'])]
    public function changePassword(Request $request): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
            return throw $this->createAccessDeniedException('Must be logged in for this operation');
        }

        // Build form.
        $formBuilder = $this->generateChangePasswordFormBuilder();
        $formBuilder->setAction($this->generateUrl('app_user_users_change_password'));
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $requestData = $request->request->all();
            $plainPassword = $requestData['form']['plainPassword'];
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->addFlash(ToastDto::FLASH_TYPE, ToastFactory::generateCustomSuccessToast('Password erfolgreich geändert'));

            return $this->redirectToRoute('app_user_result_bets_list');
        }

        return $this->render('users/changePassword.html.twig', [
            'form' => $form,
            'user' => $user
        ]);
    }

    /**
     * @return FormInterface
     */
    private function generateChangePasswordFormBuilder(): FormBuilderInterface
    {
        $formBuilder = $this->createFormBuilder();

        $formBuilder
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Neues Passwort'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Speichern'
            ]);

        return $formBuilder;
    }
}
