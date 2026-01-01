<?php

/**
 * -------------------------------------------------------------
 * Developed by Boban Milanovic BSc <boban.milanovic@gmail.com>
 *
 * Project: PawSocial Social Network
 * Description: A social network platform designed for pets, animal lovers,
 * animal shelters, and organizations to connect, share, and collaborate.
 *
 * This software is proprietary and confidential. Any use, reproduction, or
 * distribution without explicit written permission from the author is strictly prohibited.
 *
 * For licensing or collaboration inquiries, please contact:
 * Email: boban.milanovic@gmail.com
 * -------------------------------------------------------------
 *
 * Class: User
 * Description: Represents a user in the Animal Social Network platform.
 * Responsibilities:
 * - Manages user profile data (e.g., name, email, role).
 * - Provides credentials for authentication.
 * - Can own multiple animals.
 * - Manages user profile image.
 * -------------------------------------------------------------
 */



namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Vich\UploaderBundle\Mapping\Attribute as Vich;


#[ApiResource]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[Vich\Uploadable]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column(length: 20)]
    private string $accountType = 'private'; // private, organization, commercial

    #[ORM\Column(type: 'boolean')]
    private bool $isBanned = false;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lastName = null;

    // --- Address Fields ---
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $street = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $houseNumber = null;

    #[ORM\Column(length: 100, nullable: true)] // Made nullable for existing users, but forms will enforce required
    private ?string $city = null;

    #[ORM\Column(length: 20, nullable: true)] // Made nullable for existing users
    private ?string $zipCode = null;

    #[ORM\Column(length: 2, options: ['default' => 'DE'])]
    private string $country = 'DE';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $organizationName = null;

    #[Vich\UploadableField(mapping: 'user_avatar', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

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

    /** @var Collection<int, Post> */
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Post::class, orphanRemoval: true)]
    private Collection $posts;

    /** @var Collection<int, PostLike> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PostLike::class, orphanRemoval: true)]
    private Collection $postLikes;

    // Animal collection removed. Use managedAccounts to access pets.

    /** @var Collection<int, Comment> */
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Comment::class, orphanRemoval: true)]
    private Collection $comments;

    // --- Follow System ---
    /** @var Collection<int, self> */
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'following')]
    private Collection $followers;

    /** @var Collection<int, self> */
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'followers')]
    #[ORM\JoinTable(name: 'user_following')]
    private Collection $following;

    // --- Custom Theme Colors ---
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(length: 7, nullable: true)]
    private ?string $primaryColor = null;

    #[ORM\Column(length: 7, nullable: true)]
    private ?string $secondaryColor = null;

    #[ORM\Column(length: 5, options: ['default' => 'en'])]
    private string $locale = 'en';

    // --- Managed Accounts System ---

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'managedAccounts')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?self $managedBy = null;

    /** @var Collection<int, self> */
    #[ORM\OneToMany(mappedBy: 'managedBy', targetEntity: self::class)]
    private Collection $managedAccounts;

    #[ORM\OneToOne(mappedBy: 'userAccount', targetEntity: Animal::class, cascade: ['persist', 'remove'])]
    private ?Animal $animalProfile = null;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        // $this->animals = new ArrayCollection(); // Removed
        $this->postLikes = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->following = new ArrayCollection();
        $this->managedAccounts = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
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

    public function getAccountType(): string
    {
        return $this->accountType;
    }

    public function setAccountType(string $accountType): static
    {
        $this->accountType = $accountType;

        return $this;
    }

    public function isBanned(): bool
    {
        return $this->isBanned;
    }

    public function setIsBanned(bool $isBanned): static
    {
        $this->isBanned = $isBanned;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getOrganizationName(): ?string
    {
        return $this->organizationName;
    }

    public function setOrganizationName(?string $organizationName): static
    {
        $this->organizationName = $organizationName;

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
    * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since
    Symfony 7.3.
    */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);
        unset($data["\0" . self::class . "\0imageFile"]);
        unset($data["\0" . self::class . "\0coverImageFile"]);

        return $data;
    }

    #[Vich\UploadableField(mapping: 'user_cover', fileNameProperty: 'coverImageName')]
    private ?File $coverImageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $coverImageName = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $coverImageUpdatedAt = null;

    public function setCoverImageFile(?File $imageFile = null): void
    {
        $this->coverImageFile = $imageFile;

        if (null !== $imageFile) {
            $this->coverImageUpdatedAt = new \DateTimeImmutable();
        }
    }

    public function getCoverImageFile(): ?File
    {
        return $this->coverImageFile;
    }

    public function setCoverImageName(?string $coverImageName): void
    {
        $this->coverImageName = $coverImageName;
    }

    public function getCoverImageName(): ?string
    {
        return $this->coverImageName;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    /**
     * Helper to get animals from managed accounts.
     * @return Collection<int, Animal>
     */
    public function getAnimals(): Collection
    {
        $animals = new ArrayCollection();
        foreach ($this->managedAccounts as $account) {
            if ($account->getAnimalProfile()) {
                $animals->add($account->getAnimalProfile());
            }
        }
        return $animals;
    }


    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setAuthor($this);
        }

        return $this;
    }



    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getAuthor() === $this) {
                $post->setAuthor(null);
            }
        }

        return $this;
    }

    // --- Address Getters/Setters ---

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): static
    {
        $this->street = $street;
        return $this;
    }

    public function getHouseNumber(): ?string
    {
        return $this->houseNumber;
    }

    public function setHouseNumber(?string $houseNumber): static
    {
        $this->houseNumber = $houseNumber;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): static
    {
        $this->zipCode = $zipCode;
        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;
        return $this;
    }

    // --- Follow System Methods ---

    /**
     * @return Collection<int, self>
     */
    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function addFollower(self $follower): static
    {
        if (!$this->followers->contains($follower)) {
            $this->followers->add($follower);
            $follower->addFollowing($this);
        }

        return $this;
    }

    public function removeFollower(self $follower): static
    {
        if ($this->followers->removeElement($follower)) {
            $follower->removeFollowing($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getFollowing(): Collection
    {
        return $this->following;
    }

    public function addFollowing(self $userToFollow): static
    {
        if (!$this->following->contains($userToFollow)) {
            $this->following->add($userToFollow);
        }

        return $this;
    }

    public function removeFollowing(self $userToUnfollow): static
    {
        $this->following->removeElement($userToUnfollow);

        return $this;
    }

    public function isFollowing(self $user): bool
    {
        return $this->following->contains($user);
    }

    public function follow(self $user): void
    {
        $this->addFollowing($user);
    }

    public function unfollow(self $user): void
    {
        $this->removeFollowing($user);
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

        return $this;
    }

    // --- Custom Color Getters/Setters ---

    public function getPrimaryColor(): ?string
    {
        return $this->primaryColor;
    }

    public function setPrimaryColor(?string $primaryColor): static
    {
        $this->primaryColor = $primaryColor;
        return $this;
    }

    public function getSecondaryColor(): ?string
    {
        return $this->secondaryColor;
    }

    public function setSecondaryColor(?string $secondaryColor): static
    {
        $this->secondaryColor = $secondaryColor;
        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;
        return $this;
    }

    // --- Managed Accounts Methods ---

    public function getManagedBy(): ?self
    {
        return $this->managedBy;
    }

    public function setManagedBy(?self $managedBy): static
    {
        $this->managedBy = $managedBy;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getManagedAccounts(): Collection
    {
        return $this->managedAccounts;
    }

    public function addManagedAccount(self $managedAccount): static
    {
        if (!$this->managedAccounts->contains($managedAccount)) {
            $this->managedAccounts->add($managedAccount);
            $managedAccount->setManagedBy($this);
        }

        return $this;
    }

    public function removeManagedAccount(self $managedAccount): static
    {
        if ($this->managedAccounts->removeElement($managedAccount)) {
            // set the owning side to null (unless already changed)
            if ($managedAccount->getManagedBy() === $this) {
                $managedAccount->setManagedBy(null);
            }
        }

        return $this;
    }

    public function getAnimalProfile(): ?Animal
    {
        return $this->animalProfile;
    }

    public function setAnimalProfile(?Animal $animalProfile): static
    {
        // unset the owning side of the relationship if necessary
        if ($animalProfile === null && $this->animalProfile !== null) {
            $this->animalProfile->setUserAccount(null);
        }

        // set the owning side of the relationship if necessary
        if ($animalProfile !== null && $animalProfile->getUserAccount() !== $this) {
            $animalProfile->setUserAccount($this);
        }

        $this->animalProfile = $animalProfile;

        return $this;
    }

    public function isManagedBy(self $user): bool
    {
        return $this->managedBy !== null && $this->managedBy->getId() === $user->getId();
    }
}