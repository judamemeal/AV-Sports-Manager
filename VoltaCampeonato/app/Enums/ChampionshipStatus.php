<?php

namespace App\Enums;

enum ChampionshipStatus: string
{
    case Upcoming = 'upcoming';
    case Active = 'active';
    case Finished = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::Upcoming => 'Próximo',
            self::Active => 'Activo',
            self::Finished => 'Finalizado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Upcoming => 'blue',
            self::Active => 'green',
            self::Finished => 'gray',
        };
    }
}
