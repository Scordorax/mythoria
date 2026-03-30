<?php

namespace App\Factory;

class WaterCard extends AbstractCard
{
    public function getType(): string
    {
        return 'water';
    }

    /**
     * Water cards grant +10 bonus — balanced, extinguishes fire-type cards.
     */
    public function getTypeBonus(): int
    {
        return 10;
    }
}
