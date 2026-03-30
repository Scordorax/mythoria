<?php

namespace App\Entity;

use App\Repository\MatchPlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MatchPlayerRepository::class)]
class MatchPlayer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $lifePoints = null;

    #[ORM\Column]
    private ?int $energy = null;

    #[ORM\ManyToOne(inversedBy: 'matchPlayers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?GameMatch $match = null;

    #[ORM\ManyToOne(inversedBy: 'matchPlayers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $usere = null;

    #[ORM\ManyToOne(inversedBy: 'matchPlayers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Deck $deck = null;

    /**
     * @var Collection<int, MatchTurn>
     */
    #[ORM\OneToMany(targetEntity: MatchTurn::class, mappedBy: 'player')]
    private Collection $matchTurns;

    /**
     * @var Collection<int, MatchAction>
     */
    #[ORM\OneToMany(targetEntity: MatchAction::class, mappedBy: 'player')]
    private Collection $matchActions;

    #[ORM\Column(type: 'json', nullable: true)]
    private array $deckState = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private array $hand = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $activeCard = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private array $discard = [];

    public function getDeckState(): array
    {
        return $this->deckState;
    }

    public function setDeckState(array $deckState): void
    {
        $this->deckState = $deckState;
    }

    public function getHand(): array
    {
        return $this->hand;
    }

    public function setHand(array $hand): void
    {
        $this->hand = $hand;
    }

    public function getActiveCard(): ?array
    {
        return $this->activeCard;
    }

    public function setActiveCard(?array $activeCard): void
    {
        $this->activeCard = $activeCard;
    }

    public function getDiscard(): array
    {
        return $this->discard;
    }

    public function setDiscard(array $discard): void
    {
        $this->discard = $discard;
    }

    public function __construct()
    {
        $this->matchTurns = new ArrayCollection();
        $this->matchActions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLifePoints(): ?int
    {
        return $this->lifePoints;
    }

    public function setLifePoints(int $lifePoints): static
    {
        $this->lifePoints = $lifePoints;

        return $this;
    }

    public function getEnergy(): ?int
    {
        return $this->energy;
    }

    public function setEnergy(int $energy): static
    {
        $this->energy = $energy;

        return $this;
    }

    public function getMatch(): ?GameMatch
    {
        return $this->match;
    }

    public function setMatch(?GameMatch $match): static
    {
        $this->match = $match;

        return $this;
    }

    public function getUsere(): ?User
    {
        return $this->usere;
    }

    public function setUsere(?User $usere): static
    {
        $this->usere = $usere;

        return $this;
    }

    public function getDeck(): ?Deck
    {
        return $this->deck;
    }

    public function setDeck(?Deck $deck): static
    {
        $this->deck = $deck;

        return $this;
    }

    /**
     * @return Collection<int, MatchTurn>
     */
    public function getMatchTurns(): Collection
    {
        return $this->matchTurns;
    }

    public function addMatchTurn(MatchTurn $matchTurn): static
    {
        if (!$this->matchTurns->contains($matchTurn)) {
            $this->matchTurns->add($matchTurn);
            $matchTurn->setPlayer($this);
        }

        return $this;
    }

    public function removeMatchTurn(MatchTurn $matchTurn): static
    {
        if ($this->matchTurns->removeElement($matchTurn)) {
            // set the owning side to null (unless already changed)
            if ($matchTurn->getPlayer() === $this) {
                $matchTurn->setPlayer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MatchAction>
     */
    public function getMatchActions(): Collection
    {
        return $this->matchActions;
    }

    public function addMatchAction(MatchAction $matchAction): static
    {
        if (!$this->matchActions->contains($matchAction)) {
            $this->matchActions->add($matchAction);
            $matchAction->setPlayer($this);
        }

        return $this;
    }

    public function removeMatchAction(MatchAction $matchAction): static
    {
        if ($this->matchActions->removeElement($matchAction)) {
            // set the owning side to null (unless already changed)
            if ($matchAction->getPlayer() === $this) {
                $matchAction->setPlayer(null);
            }
        }

        return $this;
    }
}
