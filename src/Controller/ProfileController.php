<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\User;
use App\Enum\LanguageEnum;
use App\Form\ChangePasswordFormType;
use App\Form\UpdateUserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
class ProfileController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface      $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly SluggerInterface            $slugger,
        private readonly MailerInterface             $mailer
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
            $this->handleImageUpload($form, $this->getUser());
            $this->addFlash('success', 'Profile updated successfully!');
            $this->entityManager->flush();
        }

        return $this->render('profile/edit.html.twig', [
            'availableLanguages' => LanguageEnum::getAvailableLanguages(),
            'updateForm' => $form->createView(),
        ]);
    }

    #[Route('/media', name: 'media')]
    public function media(): Response
    {
        return $this->render('profile/media.html.twig', [
            'availableLanguages' => LanguageEnum::getAvailableLanguages(),
        ]);
    }

    #[Route('/upload-image', name: 'upload_image', methods: ['POST'])]
    public function uploadImage(Request $request): Response
    {
        $imageFile = $request->files->get('profile_image');

        if ($imageFile) {
            $this->handleImageUpload($imageFile, $this->getUser());
            $this->entityManager->flush();
        } else {
            $this->addFlash('error', 'No image uploaded.');
        }

        return $this->redirectToRoute('app_profile_media');
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
            $this->addFlash('success', 'Password updated successfully!');
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

    #[Route('/offer', name: 'offer')]
    public function offer(): Response
    {
        return $this->render('profile/offer.html.twig');
    }

    #[Route('/pay_offer', name: 'offer_pay')]
    public function payOffer(Request $request): Response
    {
        $offerId = (int)$request->request->get('offer_id');
        return $this->render('profile/pay_offer.html.twig', [
            'offer_id' => $offerId
        ]);
    }

    #[Route('/change_offer', name: 'offer_change')]
    public function changeOffer(Request $request): Response
    {
        $offerId = (int)$request->request->get('offer_id');

        if ($offerId !== 0) {
            $offer = $this->entityManager->getRepository(Offer::class)->find($offerId);
            $this->getUser()->setOffer($offer);
            $offer->addUser($this->getUser());

            try {
                $this->sendOfferChangeEmail($this->getUser(), $offer);
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('error', 'An error occurred while sending the email.');
                return $this->redirectToRoute('app_profile_offer');
            }

            $this->entityManager->flush();
            $this->addFlash('success', 'Offer changed successfully!');
        }

        return $this->redirectToRoute('app_profile_offer');
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