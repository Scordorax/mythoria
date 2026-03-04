<?php

namespace App\Entity;

use App\Repository\CardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardRepository::class)]
class Card
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    private ?string $rarity = null;

    #[ORM\Column]
    private ?int $attack = null;

    #[ORM\Column]
    private ?int $defense = null;

    #[ORM\Column]
    private ?int $hp = null;

    #[ORM\Column]
    private ?int $energyCost = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, CardEffect>
     */
    #[ORM\OneToMany(targetEntity: CardEffect::class, mappedBy: 'card')]
    private Collection $cardEffects;

    /**
     * @var Collection<int, DeckCard>
     */
    #[ORM\OneToMany(targetEntity: DeckCard::class, mappedBy: 'card')]
    private Collection $deckCards;

    /**
     * @var Collection<int, Collectionne>
     */
    #[ORM\OneToMany(targetEntity: Collectionne::class, mappedBy: 'card')]
    private Collection $collectionnes;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    public function __construct()
    {
        $this->cardEffects = new ArrayCollection();
        $this->deckCards = new ArrayCollection();
        $this->collectionnes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getRarity(): ?string
    {
        return $this->rarity;
    }

    public function setRarity(string $rarity): static
    {
        $this->rarity = $rarity;

        return $this;
    }

    public function getAttack(): ?int
    {
        return $this->attack;
    }

    public function setAttack(int $attack): static
    {
        $this->attack = $attack;

        return $this;
    }

    public function getDefense(): ?int
    {
        return $this->defense;
    }

    public function setDefense(int $defense): static
    {
        $this->defense = $defense;

        return $this;
    }

    public function getHp(): ?int
    {
        return $this->hp;
    }

    public function setHp(int $hp): static
    {
        $this->hp = $hp;

        return $this;
    }

    public function getEnergyCost(): ?int
    {
        return $this->energyCost;
    }

    public function setEnergyCost(int $energyCost): static
    {
        $this->energyCost = $energyCost;

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
     * @return Collection<int, CardEffect>
     */
    public function getCardEffects(): Collection
    {
        return $this->cardEffects;
    }

    public function addCardEffect(CardEffect $cardEffect): static
    {
        if (!$this->cardEffects->contains($cardEffect)) {
            $this->cardEffects->add($cardEffect);
            $cardEffect->setCard($this);
        }

        return $this;
    }

    public function removeCardEffect(CardEffect $cardEffect): static
    {
        if ($this->cardEffects->removeElement($cardEffect)) {
            // set the owning side to null (unless already changed)
            if ($cardEffect->getCard() === $this) {
                $cardEffect->setCard(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DeckCard>
     */
    public function getDeckCards(): Collection
    {
        return $this->deckCards;
    }

    public function addDeckCard(DeckCard $deckCard): static
    {
        if (!$this->deckCards->contains($deckCard)) {
            $this->deckCards->add($deckCard);
            $deckCard->setCard($this);
        }

        return $this;
    }

    public function removeDeckCard(DeckCard $deckCard): static
    {
        if ($this->deckCards->removeElement($deckCard)) {
            // set the owning side to null (unless already changed)
            if ($deckCard->getCard() === $this) {
                $deckCard->setCard(null);
            }
        }

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
            $collectionne->setCard($this);
        }

        return $this;
    }

    public function removeCollectionne(Collectionne $collectionne): static
    {
        if ($this->collectionnes->removeElement($collectionne)) {
            // set the owning side to null (unless already changed)
            if ($collectionne->getCard() === $this) {
                $collectionne->setCard(null);
            }
        }

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }
}
