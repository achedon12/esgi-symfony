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

    #[Route('/', name: 'index')]
    public function index(DiscussionRepository $discussionRepository): Response
    {
        $user = $this->getUser();

        $discussions = $discussionRepository->findByUser($user);

        array_map(function($discussion) use ($user) {
            if($discussion->getUserOne() === $user) {
                $discussion->setUserTwo($discussion->getUserTwo());
            } else {
                $discussion->setUserTwo($discussion->getUserOne());
            }
        }, $discussions);

        array_map(function($discussion) use ($user) {
            if($discussion->getUserOne() === $user) {
                $discussion->setUserTwo($discussion->getUserTwo());
            } else {
                $discussion->setUserTwo($discussion->getUserOne());
            }
        }, $discussions);

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'discussions' => $discussions,
            'availableLanguages' => LanguageEnum::getAvailableLanguages()
        ]);
    }

    #[Route('/filters', name: 'filters')]
    public function filters(): Response
    {
        return $this->render('profile/filters.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/update', name: 'update')]
    public function update(Request $request, SluggerInterface $slugger, EntityManagerInterface $entityManager, DiscussionRepository $discussionRepository): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UpdateUserFormType::class, $user);
        $form->handleRequest($request);

        $discussions = $discussionRepository->findByUser($user);

        array_map(function($discussion) use ($user) {
            if($discussion->getUserOne() === $user) {
                $discussion->setUserTwo($discussion->getUserTwo());
            } else {
                $discussion->setUserTwo($discussion->getUserOne());
            }
        }, $discussions);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
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
            $entityManager->flush();
        }

        return $this->render('profile/update.html.twig', [
            'updateForm' => $form->createView(),
            'user' => $user,
            'discussions' => $discussions
        ]);
    }

    #[Route('/update-password', name: 'update_password')]
    public function updatePassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // update password hash
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $form->get('newPassword')->getData())
            );

            $this->addFlash('success', 'Password updated successfully!');
            $entityManager->flush();
        }

        return $this->render('profile/update_password.html.twig', [
            'updatePasswordForm' => $form->createView(),
            'user' => $user
        ]);
    }

    #[Route('/updateLanguage', name: 'update_language',methods: ['POST'])]
    public function updateLanguage(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $user->setLanguage($request->request->get('language'));
        $entityManager->flush();

        return $this->redirectToRoute('app_profile_index');
    }
}
