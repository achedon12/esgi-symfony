<?php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::CONTROLLER, method: 'onKernelRequest')]
class UserOfferChangedListener
{

    public function __construct(private EntityManagerInterface $entityManager,
                                private LoggerInterface        $logger)
    {
    }

    public function onKernelRequest(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        $offerId = (int)$request->request->get('offer_id');

        if ($offerId !== 0) {
            $this->logger->info('---------------------[UserOfferChanged]---------------------');

        }

    }
}