<?php

namespace App\Factory;

class ElectricCard extends AbstractCard
{
    public function getType(): string
    {
        return 'electric';
    }

    /**
     * Electric cards grant +12 bonus — fast and unpredictable.
     */
    public function getTypeBonus(): int
    {
        return 12;
    }
}
