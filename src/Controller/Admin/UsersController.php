<?php

namespace App\Controller\Admin;

use App\Dto\ToastDto;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ToastFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted(User::ROLE_ADMIN)]
class UsersController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('/users', name: 'app_admin_users_list', methods: ['GET'])]
    public function list(): Response
    {
        $users = $this->userRepository->findAll();

        return $this->render('admin/users/list.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/users/new', name: 'app_admin_users_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $user = new User();

        // Build form.
        $formBuilder = $this->generateUserAddFormBuilder($user);
        $formBuilder->setAction($this->generateUrl('app_admin_users_new'));
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $requestData = $request->request->all();
            // Set admin role if admin checkbox is set.
            $user->setRoles($this->getRolesFromRequestData($requestData));
            $plainPassword = $requestData['form']['plainPassword'];
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->addFlash(ToastDto::FLASH_TYPE, ToastFactory::generateSaveSuccessfulToast());

            return $this->redirectToRoute('app_admin_users_list');
        }

        return $this->render('admin/users/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/users/edit/{id}', name: 'app_admin_users_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, $id): Response
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return throw $this->createNotFoundException('This user does not exist');
        }

        // Build form.
        $formBuilder = $this->generateUserEditFormBuilder($user);
        $formBuilder->setAction($this->generateUrl('app_admin_users_edit', ['id' => $id]));
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $requestData = $request->request->all();
            // Set admin role if admin checkbox is set.
            $user->setRoles($this->getRolesFromRequestData($requestData));
            $this->entityManager->flush();
            $this->addFlash(ToastDto::FLASH_TYPE, ToastFactory::generateSaveSuccessfulToast());

            return $this->redirectToRoute('app_admin_users_list');
        }

        return $this->render('admin/users/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/users/password/{id}', name: 'app_admin_users_change_password', methods: ['GET', 'POST'])]
    public function changePassword(Request $request, $id): Response
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return throw $this->createNotFoundException('This user does not exist');
        }

        // Build form.
        $formBuilder = $this->generateChangePasswordFormBuilder();
        $formBuilder->setAction($this->generateUrl('app_admin_users_change_password', ['id' => $id]));
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

            return $this->redirectToRoute('app_admin_users_list');
        }

        return $this->render('admin/users/changePassword.html.twig', [
            'form' => $form,
            'user' => $user
        ]);
    }

    #[Route('/users/delete/{id}', name: 'app_admin_users_delete', methods: ['GET'])]
    public function delete(Request $request, $id): Response
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return throw $this->createNotFoundException('This user does not exist');
        }

        if (in_array(User::ROLE_ADMIN, $user->getRoles())) {
            return throw new BadRequestHttpException('Admin users cannot be deleted');
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();
        $this->addFlash(ToastDto::FLASH_TYPE, ToastFactory::generateDeleteSuccessfulToast());

        return $this->redirectToRoute('app_admin_users_list');
    }

    /**
     * @param array $requestData
     * @return array
     */
    private function getRolesFromRequestData(array $requestData): array {
        $roles = [];

        if (array_key_exists('isAdmin', $requestData['form'])) {
            $isAdmin = $requestData['form']['isAdmin'];
            $roles = $isAdmin ? [User::ROLE_ADMIN] : [];
        }

        return $roles;
    }

    /**
     * @param User $user
     * @return FormInterface
     */
    private function generateUserEditFormBuilder(User $user): FormBuilderInterface
    {
        $formBuilder = $this->createFormBuilder($user);

        $formBuilder
            ->add('email', EmailType::class, ['label' => 'E-Mail-Adresse'])
            ->add('firstName', TextType::class, ['label' => 'Vorname'])
            ->add('lastName', TextType::class, ['label' => 'Nachname'])
            ->add('isActive', null, ['label' => 'Ist Aktiv?'])
            ->add('isAdmin', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'data' => in_array(User::ROLE_ADMIN, $user->getRoles()),
                'label' => 'Ist Administrator?'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Speichern'
            ]);

        return $formBuilder;
    }

    /**
     * @param User $user
     * @return FormInterface
     */
    private function generateUserAddFormBuilder(User $user): FormBuilderInterface
    {
        $formBuilder = $this->createFormBuilder($user);

        $formBuilder
            ->add('email', EmailType::class, ['label' => 'E-Mail-Adresse'])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Passwort'
            ])
            ->add('firstName', TextType::class, ['label' => 'Vorname'])
            ->add('lastName', TextType::class, ['label' => 'Nachname'])
            ->add('isActive', null, ['label' => 'Ist Aktiv?'])
            ->add('isAdmin', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'data' => in_array(User::ROLE_ADMIN, $user->getRoles()),
                'label' => 'Ist Administrator?'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Speichern'
            ]);

        return $formBuilder;
    }

    /**
     * @return FormInterface
     */
    private function generateChangePasswordFormBuilder(): FormBuilderInterface
    {
        $formBuilder = $this->createFormBuilder();

        $formBuilder
            ->add('plainPassword', TextType::class, [
                'mapped' => false,
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Speichern'
            ]);

        return $formBuilder;
    }
}
