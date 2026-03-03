<?php

namespace App\Entity;

use App\Repository\GameMatchRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameMatchRepository::class)]
class GameMatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\Column]
    private ?int $currentTurn = null;

    /**
     * @var Collection<int, MatchPlayer>
     */
    #[ORM\OneToMany(targetEntity: MatchPlayer::class, mappedBy: 'match')]
    private Collection $matchPlayers;

    /**
     * @var Collection<int, MatchTurn>
     */
    #[ORM\OneToMany(targetEntity: MatchTurn::class, mappedBy: 'match')]
    private Collection $matchTurns;

    /**
     * @var Collection<int, MatchAction>
     */
    #[ORM\OneToMany(targetEntity: MatchAction::class, mappedBy: 'match')]
    private Collection $matchActions;

    public function __construct()
    {
        $this->matchPlayers = new ArrayCollection();
        $this->matchTurns = new ArrayCollection();
        $this->matchActions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): static
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    public function getCurrentTurn(): ?int
    {
        return $this->currentTurn;
    }

    public function setCurrentTurn(int $currentTurn): static
    {
        $this->currentTurn = $currentTurn;

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
            $matchPlayer->setMatch($this);
        }

        return $this;
    }

    public function removeMatchPlayer(MatchPlayer $matchPlayer): static
    {
        if ($this->matchPlayers->removeElement($matchPlayer)) {
            // set the owning side to null (unless already changed)
            if ($matchPlayer->getMatch() === $this) {
                $matchPlayer->setMatch(null);
            }
        }

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
            $matchTurn->setMatch($this);
        }

        return $this;
    }

    public function removeMatchTurn(MatchTurn $matchTurn): static
    {
        if ($this->matchTurns->removeElement($matchTurn)) {
            // set the owning side to null (unless already changed)
            if ($matchTurn->getMatch() === $this) {
                $matchTurn->setMatch(null);
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
            $matchAction->setMatch($this);
        }

        return $this;
    }

    public function removeMatchAction(MatchAction $matchAction): static
    {
        if ($this->matchActions->removeElement($matchAction)) {
            // set the owning side to null (unless already changed)
            if ($matchAction->getMatch() === $this) {
                $matchAction->setMatch(null);
            }
        }

        return $this;
    }
}
