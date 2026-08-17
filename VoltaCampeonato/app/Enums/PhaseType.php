<?php

namespace App\Enums;

enum PhaseType: string
{
    case Group = 'group';
    case Knockout = 'knockout';
    case League = 'league';
    case PlayIn = 'play_in';
    case Final = 'final';

    public function label(): string
    {
        return match ($this) {
            self::Group => 'Fase de Grupos',
            self::Knockout => 'Eliminación Directa',
            self::League => 'Liga',
            self::PlayIn => 'Play-In',
            self::Final => 'Final',
        };
    }
}
