<?php

namespace App\Event;

use App\Entity\Offer;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;


class UserOfferChangedEvent extends Event
{
    public const  NAME = 'app.user.offer.changed';

    private User $user;
    private Offer $offer;

    public function __construct(User $user, Offer $offer)
    {
        $this->user = $user;
        $this->offer = $offer;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getOffer(): Offer
    {
        return $this->offer;
    }
}
