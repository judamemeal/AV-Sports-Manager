<?php

namespace App\Models;

use App\Enums\PhaseType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Phase extends Model
{
    use HasFactory;

    protected $fillable = [
        'championship_id',
        'tournament_format_id',
        'name',
        'type',
        'order',
        'team_count',
        'configuration',
        'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'type' => PhaseType::class,
            'configuration' => 'array',
            'is_completed' => 'boolean',
        ];
    }

    public function championship(): BelongsTo
    {
        return $this->belongsTo(Championship::class);
    }

    public function tournamentFormat(): BelongsTo
    {
        return $this->belongsTo(TournamentFormat::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class)->orderBy('round_number');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(GameMatch::class);
    }
}
