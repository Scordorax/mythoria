<?php

namespace App\Factory;

class GhostCard extends AbstractCard
{
    public function getType(): string
    {
        return 'ghost';
    }

    /**
     * Ghost cards grant +20 bonus — rare, highest type bonus in the game.
     */
    public function getTypeBonus(): int
    {
        return 20;
    }
}
