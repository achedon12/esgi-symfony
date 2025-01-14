<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\User;
use App\Event\UserOfferChangedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/offer', name: 'app_offer_')]
class OfferController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface        $mailer,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TranslatorInterface      $translator
    )
    {
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $offers = $this->entityManager->getRepository(Offer::class)->findAll();
        return $this->render('profile/userOffer/offer.html.twig', [
            'offers' => $offers
        ]);
    }

    #[Route('/pay_offer', name: 'pay')]
    public function payOffer(Request $request): Response
    {
        $offerId = (int)$request->request->get('offer_id');
        return $this->render('profile/userOffer/pay_offer.html.twig', [
            'offer_id' => $offerId
        ]);
    }

    #[Route('/change_offer', name: 'change')]
    public function changeOffer(Request $request): Response
    {
        $offerId = (int)$request->request->get('offer_id');

        if ($offerId !== 0) {
            $offer = $this->entityManager->getRepository(Offer::class)->find($offerId);
            $this->getUser()->setOffer($offer);
            $offer->addUser($this->getUser());

            $event = new UserOfferChangedEvent($this->getUser(), $offer);
            $this->eventDispatcher->dispatch($event, UserOfferChangedEvent::NAME);

            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('flash.offer.changed'));
        }

        return $this->redirectToRoute('app_offer_index');
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
