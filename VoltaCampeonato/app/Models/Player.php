<?php

namespace App\Models;

use App\Enums\PlayerPosition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'first_name',
        'last_name',
        'jersey_number',
        'position',
        'course',
        'parallel',
        'birth_date',
        'photo_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_active' => 'boolean',
            'position' => PlayerPosition::class,
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function matchEvents(): HasMany
    {
        return $this->hasMany(MatchEvent::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function goals(): HasMany
    {
        return $this->matchEvents()->where('type', 'goal');
    }

    public function yellowCards(): HasMany
    {
        return $this->matchEvents()->where('type', 'yellow_card');
    }

    public function redCards(): HasMany
    {
        return $this->matchEvents()->where('type', 'red_card');
    }
}
