<?php

namespace App\Service;

use App\Entity\MatchPlayer;
use App\Entity\MatchAction;
use App\Entity\Card;

class GameEngine
{
    /**
     * 🎴 Initialisation du match
     */
    public function initGame(MatchPlayer $player, array $deckCards): void
    {
        shuffle($deckCards);

        $player->setDeckState($deckCards);
        $player->setHand([]);
        $player->setDiscard([]);
        $player->setEnergy(0);

        // Pioche 5 cartes
        for ($i = 0; $i < 5; $i++) {
            $this->drawCard($player);
        }

        // Carte active
        $card = $this->drawCard($player);
        $player->setActiveCard($card);
    }

    /**
     * 🎴 Piocher une carte
     */
    public function drawCard(MatchPlayer $player): ?array
    {
        $deck = $player->getDeckState();

        if (count($deck) === 0) {
            return null;
        }

        $card = array_shift($deck);

        $hand = $player->getHand();
        $hand[] = $card;

        $player->setDeckState($deck);
        $player->setHand($hand);

        return $card;
    }

    /**
     * ⚡ Ajouter énergie par tour
     */
    public function addEnergy(MatchPlayer $player): void
    {
        $player->setEnergy($player->getEnergy() + 1);
    }

    /**
     * 🃏 Jouer une carte depuis la main
     */
    public function playCard(MatchPlayer $player, int $cardId): ?array
    {
        $hand = $player->getHand();

        foreach ($hand as $key => $card) {
            if ($card['id'] === $cardId) {

                if ($player->getEnergy() < $card['energyCost']) {
                    return null;
                }

                $player->setEnergy($player->getEnergy() - $card['energyCost']);

                unset($hand[$key]);
                $player->setHand(array_values($hand));

                return $card;
            }
        }

        return null;
    }

    /**
     * ⚔️ Attaque
     */
    public function attack(MatchPlayer $attacker, MatchPlayer $defender, array $card): array
    {
        $damage = max(0, $card['attack'] - ($defender->getActiveCard()['defense'] ?? 0));

        $defenderCard = $defender->getActiveCard();
        $defenderCard['hp'] -= $damage;

        $ko = false;

        if ($defenderCard['hp'] <= 0) {
            $ko = true;

            $discard = $defender->getDiscard();
            $discard[] = $defenderCard;

            $defender->setDiscard($discard);
            $defender->setActiveCard(null);
        } else {
            $defender->setActiveCard($defenderCard);
        }

        return [
            'damage' => $damage,
            'ko' => $ko
        ];
    }

    /**
     * 🔄 Tour complet joueur
     */
    public function playerTurn(MatchPlayer $player): void
    {
        $this->drawCard($player);
        $this->addEnergy($player);
    }

    /**
     * 🤖 IA intelligente
     */
    public function aiTurn(MatchPlayer $ai, MatchPlayer $enemy): array
    {
        $this->drawCard($ai);
        $this->addEnergy($ai);

        $hand = $ai->getHand();

        // Choisir meilleure carte jouable
        $bestCard = null;

        foreach ($hand as $card) {
            if ($ai->getEnergy() >= $card['energyCost']) {
                if (!$bestCard || $card['attack'] > $bestCard['attack']) {
                    $bestCard = $card;
                }
            }
        }

        if ($bestCard) {
            $this->playCard($ai, $bestCard['id']);
            return $this->attack($ai, $enemy, $bestCard);
        }

        return ['skip' => true];
    }
}