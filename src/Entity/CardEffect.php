<?php

namespace App\Entity;

use App\Repository\CardEffectRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardEffectRepository::class)]
class CardEffect
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $effectType = null;

    #[ORM\Column]
    private ?int $value = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $conditionType = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $conditionValue = null;

    #[ORM\ManyToOne(inversedBy: 'cardEffects')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Card $card = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEffectType(): ?string
    {
        return $this->effectType;
    }

    public function setEffectType(string $effectType): static
    {
        $this->effectType = $effectType;

        return $this;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getConditionType(): ?string
    {
        return $this->conditionType;
    }

    public function setConditionType(?string $conditionType): static
    {
        $this->conditionType = $conditionType;

        return $this;
    }

    public function getConditionValue(): ?string
    {
        return $this->conditionValue;
    }

    public function setConditionValue(?string $conditionValue): static
    {
        $this->conditionValue = $conditionValue;

        return $this;
    }

    public function getCard(): ?Card
    {
        return $this->card;
    }

    public function setCard(?Card $card): static
    {
        $this->card = $card;

        return $this;
    }
}
