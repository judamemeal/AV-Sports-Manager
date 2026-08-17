<?php

namespace App\Enums;

enum TournamentType: string
{
    case League = 'league';
    case Groups = 'groups';
    case Knockout = 'knockout';
    case GroupsKnockout = 'groups_knockout';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::League => 'Liga',
            self::Groups => 'Fase de Grupos',
            self::Knockout => 'Eliminación Directa',
            self::GroupsKnockout => 'Grupos + Eliminación',
            self::Custom => 'Personalizado',
        };
    }
}
