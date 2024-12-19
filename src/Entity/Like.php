<?php

namespace App\Entity;

use App\Repository\LikeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LikeRepository::class)]
#[ORM\Table(name: '`like`')]
class Like
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'likes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user_liker = null;

    #[ORM\ManyToOne(inversedBy: 'likes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user_liked = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserLiker(): ?User
    {
        return $this->user_liker;
    }

    public function setUserLiker(?User $user_liker): static
    {
        $this->user_liker = $user_liker;

        return $this;
    }

    public function getUserLiked(): ?User
    {
        return $this->user_liked;
    }

    public function setUserLiked(?User $user_liked): static
    {
        $this->user_liked = $user_liked;

        return $this;
    }
}
