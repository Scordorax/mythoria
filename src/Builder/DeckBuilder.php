<?php

namespace App\Builder;

use App\Entity\Card;
use App\Entity\Deck;
use App\Entity\DeckCard;
use App\Entity\User;

/**
 * DeckBuilder — Builder pattern for constructing a valid Deck entity.
 *
 * Usage:
 *   $deck = (new DeckBuilder())
 *       ->setName('My Fire Deck')
 *       ->setUser($user)
 *       ->addCard($card1, 2)
 *       ->addCard($card2, 3)
 *       ->build();
 *
 * build() enforces the game rule: a deck may contain at most 20 cards
 * (counting quantities across all DeckCard entries).
 */
class DeckBuilder
{
    private const MAX_CARDS = 20;

    private ?string $name = null;
    private ?User   $user = null;

    /** @var array<int, array{card: Card, quantity: int}> */
    private array $entries = [];

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Stage a card to be added to the deck.
     *
     * @param int $quantity Number of copies (must be >= 1)
     *
     * @throws \InvalidArgumentException when quantity is below 1
     */
    public function addCard(Card $card, int $quantity = 1): static
    {
        if ($quantity < 1) {
            throw new \InvalidArgumentException('Quantity must be at least 1.');
        }

        // If the card is already staged, increase its quantity instead of
        // adding a duplicate entry.
        foreach ($this->entries as &$entry) {
            if ($entry['card'] === $card) {
                $entry['quantity'] += $quantity;

                return $this;
            }
        }
        unset($entry);

        $this->entries[] = ['card' => $card, 'quantity' => $quantity];

        return $this;
    }

    /**
     * Validate the staged state and build the Deck entity with its DeckCard
     * associations already set up.
     *
     * @throws \LogicException when required fields are missing or the deck
     *                         exceeds the maximum card count.
     */
    public function build(): Deck
    {
        if ($this->name === null || trim($this->name) === '') {
            throw new \LogicException('Deck name is required.');
        }

        if ($this->user === null) {
            throw new \LogicException('A deck must belong to a user.');
        }

        $totalCards = array_sum(array_column($this->entries, 'quantity'));

        if ($totalCards > self::MAX_CARDS) {
            throw new \LogicException(
                sprintf(
                    'A deck cannot contain more than %d cards (%d requested).',
                    self::MAX_CARDS,
                    $totalCards
                )
            );
        }

        $deck = new Deck();
        $deck->setName($this->name)
             ->setUsere($this->user)
             ->setCreatedAt(new \DateTimeImmutable());

        foreach ($this->entries as ['card' => $card, 'quantity' => $quantity]) {
            $deckCard = new DeckCard();
            $deckCard->setCard($card)
                     ->setQuantity($quantity)
                     ->setDeck($deck);

            $deck->addDeckCard($deckCard);
        }

        return $deck;
    }
}
