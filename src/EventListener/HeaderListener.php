<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::RESPONSE, method: 'addHeader')]
class HeaderListener
{
    public function addHeader(ResponseEvent $event): void
    {
        $response = $event->getResponse();

        $response->headers->add([
            'X-DEVELOPED-BY' => 'leo Deroin & Mathys Farineau'
        ]);
    }
}