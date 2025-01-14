<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ControllerEvent ;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;


#[AsEventListener(event: KernelEvents::CONTROLLER, method: 'onKernelRequest', priority: -10)]
readonly class LocaleListener
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function onKernelRequest(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }
        $request->setLocale($request->getSession()?->get('_locale') ?? 'en');
        $request->setDefaultLocale($request->getSession()?->get('_default_locale') ?? 'en');
        $this->translator->setLocale($request->getSession()?->get('_default_locale') ?? 'en');
    }
}
