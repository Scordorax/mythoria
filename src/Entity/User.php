<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
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

    #[ORM\Column(length: 50)]
    private ?string $username = null;

    #[ORM\Column]
    private ?int $eloRating = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Collectionne>
     */
    #[ORM\OneToMany(targetEntity: Collectionne::class, mappedBy: 'usere')]
    private Collection $collectionnes;

    /**
     * @var Collection<int, Deck>
     */
    #[ORM\OneToMany(targetEntity: Deck::class, mappedBy: 'usere')]
    private Collection $decks;

    /**
     * @var Collection<int, MatchPlayer>
     */
    #[ORM\OneToMany(targetEntity: MatchPlayer::class, mappedBy: 'usere')]
    private Collection $matchPlayers;

    public function __construct()
    {
        $this->collectionnes = new ArrayCollection();
        $this->decks = new ArrayCollection();
        $this->matchPlayers = new ArrayCollection();
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

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEloRating(): ?int
    {
        return $this->eloRating;
    }

    public function setEloRating(int $eloRating): static
    {
        $this->eloRating = $eloRating;

        return $this;
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

    /**
     * @return Collection<int, Collectionne>
     */
    public function getCollectionnes(): Collection
    {
        return $this->collectionnes;
    }

    public function addCollectionne(Collectionne $collectionne): static
    {
        if (!$this->collectionnes->contains($collectionne)) {
            $this->collectionnes->add($collectionne);
            $collectionne->setUsere($this);
        }

        return $this;
    }

    public function removeCollectionne(Collectionne $collectionne): static
    {
        if ($this->collectionnes->removeElement($collectionne)) {
            // set the owning side to null (unless already changed)
            if ($collectionne->getUsere() === $this) {
                $collectionne->setUsere(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Deck>
     */
    public function getDecks(): Collection
    {
        return $this->decks;
    }

    public function addDeck(Deck $deck): static
    {
        if (!$this->decks->contains($deck)) {
            $this->decks->add($deck);
            $deck->setUsere($this);
        }

        return $this;
    }

    public function removeDeck(Deck $deck): static
    {
        if ($this->decks->removeElement($deck)) {
            // set the owning side to null (unless already changed)
            if ($deck->getUsere() === $this) {
                $deck->setUsere(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MatchPlayer>
     */
    public function getMatchPlayers(): Collection
    {
        return $this->matchPlayers;
    }

    public function addMatchPlayer(MatchPlayer $matchPlayer): static
    {
        if (!$this->matchPlayers->contains($matchPlayer)) {
            $this->matchPlayers->add($matchPlayer);
            $matchPlayer->setUsere($this);
        }

        return $this;
    }

    public function removeMatchPlayer(MatchPlayer $matchPlayer): static
    {
        if ($this->matchPlayers->removeElement($matchPlayer)) {
            // set the owning side to null (unless already changed)
            if ($matchPlayer->getUsere() === $this) {
                $matchPlayer->setUsere(null);
            }
        }

        return $this;
    }
}
