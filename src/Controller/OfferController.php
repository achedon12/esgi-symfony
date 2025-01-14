<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/offer', name: 'app_offer_')]
class OfferController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface        $mailer,
    )
    {
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('profile/userOffer/offer.html.twig');
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

            try {
                $this->sendOfferChangeEmail($this->getUser(), $offer);
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('error', 'An error occurred while sending the email.');
                return $this->redirectToRoute('app_offer_index');
            }

            $this->entityManager->flush();
            $this->addFlash('success', 'Offer changed successfully!');
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
