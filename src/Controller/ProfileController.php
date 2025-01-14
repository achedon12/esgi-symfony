<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\User;
use App\Enum\LanguageEnum;
use App\Form\ChangePasswordFormType;
use App\Form\UpdateUserFormType;
use App\Repository\DiscussionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profile', name: 'app_profile_')]
class ProfileController extends AbstractBaseController
{
    public function __construct(
        DiscussionRepository $discussionRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly SluggerInterface $slugger,
        private readonly MailerInterface $mailer
    ) {
        parent::__construct($discussionRepository);
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        [$user, $discussions] = $this->initializeUserAndDescription();

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'discussions' => $discussions
        ]);
    }

    #[Route('/settings', name: 'settings')]
    public function settings(): Response
    {
        [$user, $discussions] = $this->initializeUserAndDescription();

        return $this->render('profile/settings.html.twig', [
            'user' => $user,
            'discussions' => $discussions,
            'availableLanguages' => LanguageEnum::getAvailableLanguages(),
        ]);
    }

    #[Route('/edit', name: 'edit')]
    public function edit(Request $request): Response
    {
        [$user, $discussions] = $this->initializeUserAndDescription();

        $form = $this->createForm(UpdateUserFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImageUpload($form, $user);
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

    #[Route('/media', name: 'media')]
    public function media(): Response
    {
        [$user, $discussions] = $this->initializeUserAndDescription();

        return $this->render('profile/media.html.twig', [
            'user' => $user,
            'discussions' => $discussions,
            'availableLanguages' => LanguageEnum::getAvailableLanguages(),
        ]);
    }

    #[Route('/upload-image', name: 'upload_image', methods: ['POST'])]
    public function uploadImage(Request $request): Response
    {
        [$user] = $this->initializeUser();
        $imageFile = $request->files->get('profile_image');

        if ($imageFile) {
            $this->handleImageUpload($imageFile, $user);
            $this->entityManager->flush();
        } else {
            $this->addFlash('error', 'No image uploaded.');
        }

        return $this->redirectToRoute('app_profile_media');
    }

    #[Route('/update-password', name: 'update_password')]
    public function updatePassword(Request $request): Response
    {
        [$user] = $this->initializeUser();
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

    #[Route('/updateLanguage', name: 'update_language', methods: ['POST'])]
    public function updateLanguage(Request $request): Response
    {
        [$user] = $this->initializeUser();
        $language = $request->request->get('language');
        $user->setLanguage($language);
        $this->entityManager->flush();
        $request->setLocale($language);
        $request->setDefaultLocale($language);
        $request->getSession()->set('_locale', $language);
        $request->getSession()->set('_default_locale', $language);

        return $this->redirectToRoute('app_profile_settings');
    }

    #[Route('/offer', name: 'offer')]
    public function offer(): Response
    {
        [$user, $discussions] = $this->initializeUser();

        return $this->render('profile/offer.html.twig', [
            'user' => $user,
            'discussions' => $discussions,
        ]);
    }

    #[Route('/pay_offer', name: 'offer_pay')]
    public function payOffer(Request $request): Response
    {
        [$user, $discussions] = $this->initializeUser();
        $offerId = (int) $request->request->get('offer_id');

        return $this->render('profile/pay_offer.html.twig', [
            'user' => $user,
            'discussions' => $discussions,
            'offer_id' => $offerId
        ]);
    }

    #[Route('/change_offer', name: 'offer_change')]
    public function changeOffer(Request $request): Response
    {
        [$user, $discussions] = $this->initializeUser();
        $offerId = (int) $request->request->get('offer_id');

        if ($offerId !== 0) {
            $offer = $this->entityManager->getRepository(Offer::class)->find($offerId);
            $user->setOffer($offer);
            $offer->addUser($user);

            try {
                $this->sendOfferChangeEmail($user, $offer);
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('error', 'An error occurred while sending the email.');
                return $this->redirectToRoute('app_profile_offer', [
                    'user' => $user,
                    'discussions' => $discussions,
                ]);
            }

            $this->entityManager->flush();
            $this->addFlash('success', 'Offer changed successfully!');
        }

        return $this->redirectToRoute('app_profile_offer', [
            'user' => $user,
            'discussions' => $discussions,
        ]);
    }

    private function handleImageUpload($imageFile, User $user): void
    {
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

        try {
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
        }

        $user->setImage($newFilename);
    }

    private function sendOfferChangeEmail(User $user, Offer $offer): void
    {
        $email = (new Email())
            ->from('no-reply@tindoo.com')
            ->to($user->getEmail())
            ->subject('Offer changed')
            ->html($this->renderView('emails/payment_confirmation.html.twig', [
                'user' => $user,
                'offer' => $offer,
                'transaction_id' => uniqid(),
                'amount' => $offer->getPrice(),
                'currency' => 'EUR'
            ]));

        $this->mailer->send($email);
    }
}