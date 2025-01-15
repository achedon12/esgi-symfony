<?php

namespace App\EventListener;

use App\Event\UserCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[AsEventListener(event: UserCreatedEvent::NAME, method: 'onUserCreated')]
readonly class UserCreatedListener
{
    public function __construct(private MailerInterface $mailer,
                                private Environment     $twig)
    {
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function onUserCreated(UserCreatedEvent $event): void
    {
        $user = $event->getUser();

        try {
            $email = (new Email())
                ->from('no-reply@tindoo.com')
                ->to($user->getEmail())
                ->subject('Welcome to Tindoo!')
                ->html($this->twig->render('emails/registration.html.twig', ['user' => $user]));

            $this->mailer->send($email);
        } catch (TransportExceptionInterface) {
        }
    }
}
