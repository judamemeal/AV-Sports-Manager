<?php

namespace App\Models;

use App\Enums\ChampionshipStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Championship extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'year',
        'sport',
        'category',
        'course_level',
        'start_date',
        'end_date',
        'description',
        'regulations',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => ChampionshipStatus::class,
        ];
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(GameMatch::class);
    }

    public function phases(): HasMany
    {
        return $this->hasMany(Phase::class)->orderBy('order');
    }

    public function standings(): HasMany
    {
        return $this->hasMany(Standing::class);
    }

    public function tournamentFormats(): HasMany
    {
        return $this->hasMany(TournamentFormat::class);
    }

    public function activeTournamentFormat()
    {
        return $this->tournamentFormats()->latest()->first();
    }
}
