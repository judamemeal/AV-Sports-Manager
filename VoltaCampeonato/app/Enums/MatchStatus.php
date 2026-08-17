<?php

namespace App\Enums;

enum MatchStatus: string
{
    case Scheduled = 'scheduled';
    case InProgress = 'in_progress';
    case Finished = 'finished';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Programado',
            self::InProgress => 'En Juego',
            self::Finished => 'Finalizado',
            self::Suspended => 'Suspendido',
            self::Cancelled => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Scheduled => 'blue',
            self::InProgress => 'green',
            self::Finished => 'gray',
            self::Suspended => 'yellow',
            self::Cancelled => 'red',
        };
    }
}
