<?php

namespace App\Factory;

class EarthCard extends AbstractCard
{
    public function getType(): string
    {
        return 'earth';
    }

    /**
     * Earth cards grant +8 bonus — durable, high-HP tanking style.
     */
    public function getTypeBonus(): int
    {
        return 8;
    }
}
