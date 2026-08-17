<?php

namespace App\Enums;

enum FormatStatus: string
{
    case Draft = 'draft';
    case Configured = 'configured';
    case Generated = 'generated';
    case InProgress = 'in_progress';
    case Finished = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Configured => 'Configurado',
            self::Generated => 'Generado',
            self::InProgress => 'En Curso',
            self::Finished => 'Finalizado',
        };
    }
}
