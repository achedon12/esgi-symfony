<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

#[AsEventListener(event: KernelEvents::REQUEST, method: 'onKernelRequest')]
class LocaleListener
{
    private Security $security;
    private string $defaultLocale;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, Security $security, string $defaultLocale = 'en')
    {
        $this->security = $security;
        $this->defaultLocale = $defaultLocale;
        $this->logger = $logger;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest()) {
            return;
        }

        $user = $this->security->getUser();

        $this->logger->info('-------------------[LocaleListener]-------------------');

        //TODO: fix this
        if (is_null($user)) {
            $this->logger->info('No user is currently authenticated.');
            $this->logger->info('user :' . $request->getUser());
        } else {
            $this->logger->info('Authenticated user: ' . $user->getUserIdentifier());
        }

        if ($user && method_exists($user, 'getLanguage')) {
            $locale = $user->getLanguage();
        } else {
            $locale = $this->defaultLocale;
        }

        $this->logger->info('Locale set to: ' . $locale);

        $request->setLocale($locale);
    }
}
