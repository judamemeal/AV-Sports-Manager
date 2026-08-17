<?php

namespace App\Models;

use App\Enums\FormatStatus;
use App\Enums\TournamentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TournamentFormat extends Model
{
    use HasFactory;

    protected $fillable = [
        'championship_id',
        'type',
        'configuration',
        'status',
        'is_round_trip',
    ];

    protected function casts(): array
    {
        return [
            'type' => TournamentType::class,
            'status' => FormatStatus::class,
            'configuration' => 'array',
            'is_round_trip' => 'boolean',
        ];
    }

    public function championship(): BelongsTo
    {
        return $this->belongsTo(Championship::class);
    }

    public function phases(): HasMany
    {
        return $this->hasMany(Phase::class)->orderBy('order');
    }
}
