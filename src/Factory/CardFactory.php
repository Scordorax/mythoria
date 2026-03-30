<?php

namespace App\Factory;

/**
 * CardFactory — central entry point for the Factory pattern.
 *
 * Usage:
 *   $card = CardFactory::create('fire', 'Inferno Drake', 'A fierce dragon', 'rare', 80, 40, 120, 3);
 *
 * The caller only knows AbstractCard; which concrete subclass is returned
 * is an implementation detail hidden inside this factory (encapsulation).
 */
class CardFactory
{
    /**
     * @param string $type        One of: fire, water, electric, earth, ghost
     * @param string $name        Card name
     * @param string $description Flavour text
     * @param string $rarity      common | uncommon | rare | legendary
     * @param int    $attack      Base attack points
     * @param int    $defense     Base defence points
     * @param int    $hp          Hit points
     * @param int    $energyCost  Energy cost to play the card
     *
     * @throws \InvalidArgumentException for unknown types
     */
    public static function create(
        string $type,
        string $name,
        string $description,
        string $rarity,
        int $attack,
        int $defense,
        int $hp,
        int $energyCost
    ): AbstractCard {
        $args = [$name, $description, $rarity, $attack, $defense, $hp, $energyCost];

        return match (strtolower($type)) {
            'fire'     => new FireCard(...$args),
            'water'    => new WaterCard(...$args),
            'electric' => new ElectricCard(...$args),
            'earth'    => new EarthCard(...$args),
            'ghost'    => new GhostCard(...$args),
            default    => throw new \InvalidArgumentException(
                sprintf('Unknown card type "%s". Supported types: fire, water, electric, earth, ghost.', $type)
            ),
        };
    }
}
