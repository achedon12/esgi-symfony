<?php

namespace App\EventListener;

use App\Event\UserOfferChangedEvent;
use Exception;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

#[AsEventListener(event: UserOfferChangedEvent::NAME, method: 'onOfferChanged')]
readonly class UserOfferChangedListener
{
    public function __construct(private MailerInterface $mailer,
                                private Environment     $twig)
    {
    }

    public function onOfferChanged(UserOfferChangedEvent $event): void
    {
        $user = $event->getUser();
        $offer = $event->getOffer();

        try {
            $email = (new Email())
                ->from('no-reply@tindoo.com')
                ->to($user->getEmail())
                ->subject('Offer changed')
                ->html($this->twig->render('emails/payment_confirmation.html.twig', [
                    'user' => $user,
                    'transaction_id' => uniqid(),
                    'amount' => $offer->getPrice(),
                    'currency' => 'EUR'
                ]));

            $this->mailer->send($email);
        } catch (Exception|TransportExceptionInterface) {
        }

    }
}