<?php

namespace App\Service;

use App\Entity\MatchPlayer;

class GameEngine
{
    public function initGame(MatchPlayer $player, array $deck): void
    {
        shuffle($deck);

        $player->setDeckState($deck);
        $player->setHand([]);
        $player->setEnergy(0);
        $player->setDeadCards([]);

        for ($i = 0; $i < 5; $i++) {
            $this->drawCard($player);
        }
    }

    public function drawCard(MatchPlayer $player): ?array
    {
        $deck = $player->getDeckState();

        if (empty($deck)) return null;

        $card = array_shift($deck);

        $hand = $player->getHand();
        $hand[] = $card;

        $player->setDeckState($deck);
        $player->setHand($hand);

        return $card;
    }

    public function addEnergy(MatchPlayer $player): void
    {
        $player->setEnergy($player->getEnergy() + 1);
    }

    public function playCard(MatchPlayer $player, int $cardId): ?array
    {
        foreach ($player->getHand() as $key => $card) {
            if ($card['id'] === $cardId) {

                if ($player->getEnergy() < $card['energyCost']) {
                    return null;
                }

                $player->setEnergy($player->getEnergy() - $card['energyCost']);

                $hand = $player->getHand();
                unset($hand[$key]);

                $player->setHand(array_values($hand));

                return $card;
            }
        }

        return null;
    }

    public function attack(MatchPlayer $attacker, MatchPlayer $defender, array $card): array
    {
        $defCard = $defender->getActiveCard();

        if (!$defCard) {
            return ['damage' => 0, 'ko' => false];
        }

        // 🔥 Calcul des dégâts
        $damage = max(0, $card['attack'] - $defCard['defense']);

        $defCard['hp'] -= $damage;

        $ko = false;

        if ($defCard['hp'] <= 0) {
            $ko = true;

            // ✅ Ajout dans cartes mortes
            $deadCards = $defender->getDeadCards();
            $deadCards[] = $defCard;
            $defender->setDeadCards($deadCards);

            // ❌ suppression de la carte active
            $defender->setActiveCard(null);

        } else {
            // 🔄 mise à jour de la carte
            $defender->setActiveCard($defCard);
        }

        return [
            'damage' => $damage,
            'ko' => $ko
        ];
    }

    public function aiTurn(MatchPlayer $ai, MatchPlayer $enemy): array
    {
        // 🎯 pioche + énergie
        $this->drawCard($ai);
        $this->addEnergy($ai);

        $best = null;

        // 🤖 choix de la meilleure carte jouable
        foreach ($ai->getHand() as $card) {

            if ($ai->getEnergy() < $card['energyCost']) {
                continue;
            }

            if (!$best || $card['attack'] > $best['attack']) {
                $best = $card;
            }
        }

        // 🎴 si une carte est jouable
        if ($best) {

            $this->playCard($ai, $best['id']);

            return $this->attack($ai, $enemy, $best);
        }

        return ['skip' => true];
    }
}