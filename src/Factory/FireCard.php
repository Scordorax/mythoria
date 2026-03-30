<?php

namespace App\Factory;

class FireCard extends AbstractCard
{
    public function getType(): string
    {
        return 'fire';
    }

    /**
     * Fire cards deal +15 bonus attack damage — high aggression, low defence.
     */
    public function getTypeBonus(): int
    {
        return 15;
    }
}
