<?php

namespace App\Factory;

use App\Entity\Card;

/**
 * AbstractCard — base class for all typed card representations.
 *
 * Demonstrates:
 *  - Abstraction  : declares getTypeBonus() / getType() as abstract contracts
 *  - Inheritance  : concrete subclasses extend this class
 *  - Polymorphism : callers work with AbstractCard; behaviour differs per subclass
 *  - Encapsulation: all properties are private, exposed only through getters/setters
 */
abstract class AbstractCard
{
    private string $name;
    private string $description;
    private string $rarity;
    private int $attack;
    private int $defense;
    private int $hp;
    private int $energyCost;

    public function __construct(
        string $name,
        string $description,
        string $rarity,
        int $attack,
        int $defense,
        int $hp,
        int $energyCost
    ) {
        $this->name        = $name;
        $this->description = $description;
        $this->rarity      = $rarity;
        $this->attack      = $attack;
        $this->defense     = $defense;
        $this->hp          = $hp;
        $this->energyCost  = $energyCost;
    }

    // ------------------------------------------------------------------ //
    //  Abstract contract — every subclass MUST define its type identity   //
    //  and the bonus it grants.                                            //
    // ------------------------------------------------------------------ //

    abstract public function getType(): string;

    abstract public function getTypeBonus(): int;

    // ------------------------------------------------------------------ //
    //  Derived behaviour — uses polymorphism via getTypeBonus()           //
    // ------------------------------------------------------------------ //

    /**
     * Returns attack + the subclass-specific type bonus.
     */
    public function getEffectiveAttack(): int
    {
        return $this->attack + $this->getTypeBonus();
    }

    // ------------------------------------------------------------------ //
    //  Hydration helper — converts this value object into a Doctrine      //
    //  Card entity ready for persistence.                                 //
    // ------------------------------------------------------------------ //

    public function toEntity(): Card
    {
        $card = new Card();
        $card->setName($this->name)
             ->setDescription($this->description)
             ->setRarity($this->rarity)
             ->setAttack($this->attack)
             ->setDefense($this->defense)
             ->setHp($this->hp)
             ->setEnergyCost($this->energyCost)
             ->setCreatedAt(new \DateTimeImmutable());

        return $card;
    }

    // ------------------------------------------------------------------ //
    //  Getters (encapsulation: private fields, public read access)        //
    // ------------------------------------------------------------------ //

    public function getName(): string        { return $this->name; }
    public function getDescription(): string { return $this->description; }
    public function getRarity(): string      { return $this->rarity; }
    public function getAttack(): int         { return $this->attack; }
    public function getDefense(): int        { return $this->defense; }
    public function getHp(): int             { return $this->hp; }
    public function getEnergyCost(): int     { return $this->energyCost; }
}
