<?php

namespace App\Controller;

use App\Enum\LanguageEnum;
use App\Form\ChangePasswordFormType;
use App\Form\UpdateUserFormType;
use App\Repository\DiscussionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profile', name: 'app_profile_')]
class ProfileController extends AbstractController
{

    public function __construct(private readonly DiscussionRepository $discussionRepository,
                                private readonly EntityManagerInterface $entityManager,
                                private readonly UserPasswordHasherInterface $userPasswordHasher,
                                private readonly SluggerInterface $slugger)
    {
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $user = $this->getUser();

        $discussions = $this->discussionRepository->findByUser($user);

        array_map(function($discussion) use ($user) {
            if($discussion->getUserOne() === $user) {
                $discussion->setUserTwo($discussion->getUserTwo());
            } else {
                $discussion->setUserTwo($discussion->getUserOne());
            }
        }, $discussions);

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'discussions' => $discussions
        ]);
    }

    #[Route('/settings', name: 'settings')]
    public function settings(): Response
    {
        $user = $this->getUser();

        $discussions = $this->discussionRepository->findByUser($user);

        array_map(function($discussion) use ($user) {
            if($discussion->getUserOne() === $user) {
                $discussion->setUserTwo($discussion->getUserTwo());
            } else {
                $discussion->setUserTwo($discussion->getUserOne());
            }
        }, $discussions);

        return $this->render('profile/settings.html.twig', [
            'user' => $user,
            'discussions' => $discussions,
            'availableLanguages' => LanguageEnum::getAvailableLanguages()
        ]);
    }

    #[Route('/edit', name: 'edit')]
    public function edit(Request $request): Response
    {
        $user = $this->getUser();

        $discussions = $this->discussionRepository->findByUser($user);

        array_map(function($discussion) use ($user) {
            if($discussion->getUserOne() === $user) {
                $discussion->setUserTwo($discussion->getUserTwo());
            } else {
                $discussion->setUserTwo($discussion->getUserOne());
            }
        }, $discussions);

        $form = $this->createForm(UpdateUserFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    // save new image
                    $imageFile->move(
                        $this->getParameter('profile_images_directory'),
                        $newFilename
                    );

                    if ($user->getImage()) {
                        $existingImagePath = $this->getParameter('profile_images_directory') . '/' . $user->getImage();
                        if (file_exists($existingImagePath)) {
                            unlink($existingImagePath);
                        }
                    }
                } catch (FileException $e) {
                    $this->addFlash('error', 'An error occurred while uploading the image.');
                    return $this->redirectToRoute('profile_show');
                }

                $user->setImage($newFilename);
            }

            $this->addFlash('success', 'Profile updated successfully!');
            $this->entityManager->flush();
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'discussions' => $discussions,
            'availableLanguages' => LanguageEnum::getAvailableLanguages(),
            'updateForm' => $form->createView(),
        ]);
    }

    #[Route('/filters', name: 'filters')]
    public function filters(): Response
    {
        return $this->render('profile/filters.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/update-password', name: 'update_password')]
    public function updatePassword(Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // update password hash
            $user->setPassword(
                $this->userPasswordHasher->hashPassword($user, $form->get('newPassword')->getData())
            );

            $this->addFlash('success', 'Password updated successfully!');
            $this->entityManager->flush();
        }

        return $this->render('profile/update_password.html.twig', [
            'updatePasswordForm' => $form->createView(),
            'user' => $user
        ]);
    }

    #[Route('/updateLanguage', name: 'update_language',methods: ['POST'])]
    public function updateLanguage(Request $request): Response
    {
        $user = $this->getUser();
        $user->setLanguage($language = $request->request->get('language'));
        $this->entityManager->flush();
        $request->setLocale($language);
        $request->setDefaultLocale($language);
        $request->getSession()->set('_locale', $language);
        $request->getSession()->set('_default_locale', $language);


        return $this->redirectToRoute('app_profile_index');
    }
}
