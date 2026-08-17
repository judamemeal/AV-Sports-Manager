<?php

namespace App\Enums;

enum MatchEventType: string
{
    case Goal = 'goal';
    case YellowCard = 'yellow_card';
    case RedCard = 'red_card';
    case Substitution = 'substitution';

    public function label(): string
    {
        return match ($this) {
            self::Goal => 'Gol',
            self::YellowCard => 'Tarjeta Amarilla',
            self::RedCard => 'Tarjeta Roja',
            self::Substitution => 'Cambio',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Goal => '⚽',
            self::YellowCard => '🟨',
            self::RedCard => '🟥',
            self::Substitution => '🔄',
        };
    }
}
