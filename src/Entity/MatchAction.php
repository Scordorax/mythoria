<?php

namespace App\Entity;

use App\Repository\MatchActionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MatchActionRepository::class)]
class MatchAction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $actionType = null;

    #[ORM\Column(type: 'json')]
    private array $payload = [];

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'matchActions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?GameMatch $match = null;

    #[ORM\ManyToOne(inversedBy: 'matchActions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MatchPlayer $player = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActionType(): ?string
    {
        return $this->actionType;
    }

    public function setActionType(string $actionType): static
    {
        $this->actionType = $actionType;

        return $this;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): static
    {
        $this->payload = $payload;

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
