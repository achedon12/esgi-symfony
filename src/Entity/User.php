<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Trait\TimeStampTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimeStampTrait;

    public const GENDER_MALE = 'male';
    public const GENDER_FEMALE = 'female';
    public const GENDER_OTHER = 'other';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, Like>
     */
    #[ORM\OneToMany(targetEntity: Like::class, mappedBy: 'user_liker')]
    private Collection $likes;

    #[ORM\OneToMany(targetEntity: Like::class, mappedBy: 'user_liked')]
    private Collection $liked_by;

    #[ORM\Column(length: 255)]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    private ?string $profilePicture = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 16, nullable: true)]
    private ?string $longitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 16, nullable: true)]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $birthdate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $score = null;

    #[ORM\Column]
    private array $interests = [];

    #[ORM\Column(type: Types::TEXT)]
    private ?string $bio = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $gender = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $language = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sexualOrientation = null;

    #[ORM\ManyToOne(targetEntity: Offer::class, inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Offer $offer = null;

    /**
     * @var Collection<int, Discussion>
     */
    #[ORM\OneToMany(targetEntity: Discussion::class, mappedBy: 'userOne')]
    private Collection $discussionsAsUserOne;

    #[ORM\OneToMany(targetEntity: Discussion::class, mappedBy: 'userTwo')]
    private Collection $discussionsAsUserTwo;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $verifiedAccount = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $verificationToken = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $additiveImages = null;

    public function __construct()
    {
        $this->likes = new ArrayCollection();
        $this->discussionsAsUserOne = new ArrayCollection();
        $this->discussionsAsUserTwo = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getProfilePicture(): string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(string $image): static
    {
        if ($this->profilePicture) {
            unlink($this->profilePicture);
        }
        $this->profilePicture = $image;
        return $this;
    }


    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(\DateTimeInterface $birthdate): static
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getAge(): int
    {
        $now = new \DateTime();
        $interval = $this->birthdate->diff($now);

        return $interval->y;
    }

    public function getScore(): ?string
    {
        return $this->score;
    }

    public function setScore(string $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getInterests(): array
    {
        return $this->interests;
    }

    public function setInterests(array $interests): static
    {
        $this->interests = $interests;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender($gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return Collection<int, Like>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Like $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setUserLiker($this);
        }

        return $this;
    }

    public function removeLike(Like $like): static
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getUserLiker() === $this) {
                $like->setUserLiker(null);
            }
        }

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): static
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return Collection<int, Like>
     */
    public function getLikedBy(): Collection {
        return $this->liked_by;
    }

    public function addLikedBy(Like $like): static {
        if (!$this->liked_by->contains($like)) {
            $this->liked_by->add($like);
            $like->setUserLiked($this);
        }

        return $this;
    }

    public function removeLikedBy(Like $like): static {
        if ($this->liked_by->removeElement($like)) {
            if ($like->getUserLiked() === $this) {
                $like->setUserLiked(null);
            }
        }

        return $this;
    }

    public function getSexualOrientation(): ?string
    {
        return $this->sexualOrientation;
    }

    public function setSexualOrientation(?string $sexualOrientation): static
    {
        $this->sexualOrientation = $sexualOrientation;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }

    public function getOffer(): ?Offer
    {
        return $this->offer;
    }

    public function setOffer(?Offer $offer): static
    {
        $this->offer = $offer;

        return $this;
    }

    public function getVerifiedAccount(): ?bool
    {
        return $this->verifiedAccount;
    }

    public function setVerifiedAccount(bool $verifiedAccount): static
    {
        $this->verifiedAccount = $verifiedAccount;

        return $this;
    }

    public function getVerificationToken(): ?string
    {
        return $this->verificationToken;
    }

    public function setVerificationToken(?string $verificationToken): static
    {
        $this->verificationToken = $verificationToken;

        return $this;
    }


    /**
     * @return Collection<int, Discussion>
     */
    public function getDiscussionsAsUserOne(): Collection
    {
        return $this->discussionsAsUserOne;
    }

    public function addDiscussionAsUserOne(Discussion $discussion): static
    {
        if (!$this->discussionsAsUserOne->contains($discussion)) {
            $this->discussionsAsUserOne->add($discussion);
            $discussion->setUserOne($this);
        }

        return $this;
    }

    public function removeDiscussionAsUserOne(Discussion $discussion): static
    {
        if ($this->discussionsAsUserOne->removeElement($discussion)) {
            if ($discussion->getUserOne() === $this) {
                $discussion->setUserOne(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Discussion>
     */
    public function getDiscussionsAsUserTwo(): Collection
    {
        return $this->discussionsAsUserTwo;
    }

    public function addDiscussionAsUserTwo(Discussion $discussion): static
    {
        if (!$this->discussionsAsUserTwo->contains($discussion)) {
            $this->discussionsAsUserTwo->add($discussion);
            $discussion->setUserTwo($this);
        }

        return $this;
    }

    public function removeDiscussionAsUserTwo(Discussion $discussion): static
    {
        if ($this->discussionsAsUserTwo->removeElement($discussion)) {
            if ($discussion->getUserTwo() === $this) {
                $discussion->setUserTwo(null);
            }
        }

        return $this;
    }

    public function getDiscussions(): array
    {
        $discussions = array_merge($this->discussionsAsUserOne->toArray(), $this->discussionsAsUserTwo->toArray());
        array_map(function ($discussion) {
            if ($discussion->getUserOne() === $this) {
                $discussion->setUserTwo($discussion->getUserTwo());
            } else {
                $discussion->setUserTwo($discussion->getUserOne());
            }
        }, $discussions);

        return $discussions;
    }

    public function getAdditiveImages(): ?array
    {
        return $this->additiveImages;
    }

    public function setAdditiveImages(?array $additiveImages): static
    {
        $this->additiveImages = $additiveImages;

        return $this;
    }

    public function addAdditiveImage(string $additiveImage): static
    {
        $this->additiveImages[] = $additiveImage;

        return $this;
    }

    public function removeAdditiveImage(string $additiveImage): static
    {
        $key = array_search($additiveImage, $this->additiveImages);
        if ($key !== false) {
            unset($this->additiveImages[$key]);
        }

        return $this;
    }

    public function getName(): string
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }

    /**
     * @return Collection<int, Like>
     */
    public function getMatches(): Collection
    {
        return $this->likes->filter(function (Like $like) {
            return $this->liked_by->contains($like);
        });
    }
}
