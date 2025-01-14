<?php

namespace App\Controller;

use App\Entity\User;
use App\Event\UserCreatedEvent;
use App\Form\RegistrationFormType;
use App\Repository\OfferRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager,
                                private readonly Security $security,
                                private readonly UserPasswordHasherInterface $userPasswordHasher,
                                private readonly OfferRepository $offerRepository,
                                private readonly EventDispatcherInterface $eventDispatcher
                )
    {
    }

    /**
     * @param Request $request
     * @return Response
     * @throws RandomException
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword($this->userPasswordHasher->hashPassword($user, $plainPassword))
                ->setOffer($this->offerRepository->findOneBy(['name' => 'Basic']))
                ->setRoles(['ROLE_USER'])
                ->setScore(50)
                ->setBio('')
                ->setVerifiedAccount(false)
                ->setCreationDate(new DateTimeImmutable())
                ->setLanguage('en')
                ->setVerificationToken(bin2hex(random_bytes(32)));

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $event = new UserCreatedEvent($user);
            $this->eventDispatcher->dispatch($event, UserCreatedEvent::NAME);

            return $this->security->login($user, 'form_login', 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/{token}', name: 'app_account_confirmation')]
    public function verifyAccount(string $token): Response
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['verificationToken' => $token]);

        if ($user) {
            $user->setVerifiedAccount(true)
                ->setVerificationToken(null);

            $this->entityManager->flush();

            $this->addFlash('success', 'Your account has been verified. You can now log in.');

            return $this->render('registration/verified.html.twig');
        }

        if ($this->getUser()) {
            return $this->redirectToRoute('app_home_index');
        }

        return $this->redirectToRoute('app_register');
    }
}
