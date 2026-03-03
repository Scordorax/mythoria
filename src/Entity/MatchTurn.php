<?php

namespace App\Entity;

use App\Repository\MatchTurnRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MatchTurnRepository::class)]
class MatchTurn
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $turnNumber = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\ManyToOne(inversedBy: 'matchTurns')]
    #[ORM\JoinColumn(nullable: false)]
    private ?GameMatch $match = null;

    #[ORM\ManyToOne(inversedBy: 'matchTurns')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MatchPlayer $player = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTurnNumber(): ?int
    {
        return $this->turnNumber;
    }

    public function setTurnNumber(int $turnNumber): static
    {
        $this->turnNumber = $turnNumber;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): static
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

    public function getMatch(): ?GameMatch
    {
        return $this->match;
    }

    public function setMatch(?GameMatch $match): static
    {
        $this->match = $match;

        return $this;
    }

    public function getPlayer(): ?MatchPlayer
    {
        return $this->player;
    }

    public function setPlayer(?MatchPlayer $player): static
    {
        $this->player = $player;

        return $this;
    }
}
