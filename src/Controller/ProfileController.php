<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\LanguageEnum;
use App\Form\ChangePasswordFormType;
use App\Form\UpdateUserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/profile', name: 'app_profile_')]
class ProfileController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface      $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly SluggerInterface            $slugger,
        private readonly TranslatorInterface         $translator
    )
    {
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig');
    }

    #[Route('/settings', name: 'settings')]
    public function settings(): Response
    {
        return $this->render('profile/settings.html.twig', [
            'availableLanguages' => LanguageEnum::getAvailableLanguages(),
        ]);
    }

    #[Route('/edit', name: 'edit')]
    public function edit(Request $request): Response
    {
        $form = $this->createForm(UpdateUserFormType::class, $this->getUser());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', $this->translator->trans('flash.account.updated'));
            $this->entityManager->flush();
        }

        return $this->render('profile/userEdit/userEdit.html.twig', [
            'availableLanguages' => LanguageEnum::getAvailableLanguages(),
            'updateForm' => $form->createView(),
        ]);
    }

    #[Route('/media', name: 'media')]
    public function media(): Response
    {
        return $this->render('profile/userMedia/media.html.twig', [
            'availableLanguages' => LanguageEnum::getAvailableLanguages(),
        ]);
    }

    #[Route('/upload-image', name: 'upload_image', methods: ['POST'])]
    public function uploadImage(Request $request): Response
    {
        $imageFile = $request->files->get('profile_image');
        $isAdditiveImage = $request->request->get('isAdditiveImage');

        if ($imageFile) {
            $this->handleImageUpload($imageFile, $this->getUser(), $isAdditiveImage === 'true');
            $this->entityManager->flush();
        } else {
            $this->addFlash('error', $this->translator->trans('flash.account.image.errorEmpty'));
        }

        if ($isAdditiveImage === 'true') {
            return $this->redirectToRoute('app_profile_index');
        } else {
            return $this->redirectToRoute('app_profile_edit');
        }
    }

    #[Route('/delete-image', name: 'delete_image', methods: ['POST'])]
    public function deleteImage(Request $request): Response
    {
        $imageToDelete = $request->request->get('image');
        $this->getUser()->removeAdditiveImage($imageToDelete);

        $this->entityManager->flush();

        return $this->redirectToRoute('app_profile_index');
    }

    #[Route('/update-password', name: 'update_password')]
    public function updatePassword(Request $request): Response
    {
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getUser()->setPassword(
                $this->userPasswordHasher->hashPassword($this->getUser(), $form->get('newPassword')->getData())
            );
            $this->addFlash('success', $this->translator->trans('flash.account.updatedPassword'));
            $this->entityManager->flush();
        }

        return $this->render('profile/update_password.html.twig', [
            'updatePasswordForm' => $form->createView()
        ]);
    }

    #[Route('/updateLanguage', name: 'update_language', methods: ['POST'])]
    public function updateLanguage(Request $request): Response
    {
        $language = $request->request->get('language');
        $this->getUser()->setLanguage($language);
        $this->entityManager->flush();
        $request->setLocale($language);
        $request->setDefaultLocale($language);
        $request->getSession()->set('_locale', $language);
        $request->getSession()->set('_default_locale', $language);

        return $this->redirectToRoute('app_profile_settings');
    }

    private function handleImageUpload($imageFile, User $user, bool $isAdditive): void
    {
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

        try {
            $imageFile->move(
                $this->getParameter('profile_images_directory'),
                $newFilename
            );

            if ($isAdditive) {
                $user->addAdditiveImage($newFilename);
            } else {
                $user->setProfilePicture($newFilename);
            }
        } catch (FileException $e) {
            $this->addFlash('error', $this->translator->trans('flash.account.image.errorUpload'));
        }
    }
}