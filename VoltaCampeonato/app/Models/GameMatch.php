<?php

namespace App\Models;

use App\Enums\MatchStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameMatch extends Model
{
    use HasFactory;

    protected $table = 'game_matches';

    protected $fillable = [
        'championship_id',
        'phase_id',
        'group_id',
        'round_id',
        'home_team_id',
        'away_team_id',
        'match_date',
        'match_time',
        'venue',
        'referee',
        'status',
        'home_score',
        'away_score',
        'match_duration',
        'bracket_position',
        'next_match_id',
    ];

    protected function casts(): array
    {
        return [
            'match_date' => 'date',
            'status' => MatchStatus::class,
            'home_score' => 'integer',
            'away_score' => 'integer',
            'match_duration' => 'integer',
            'bracket_position' => 'integer',
        ];
    }

    public function championship(): BelongsTo
    {
        return $this->belongsTo(Championship::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(Phase::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(MatchEvent::class)->orderBy('minute');
    }

    public function nextMatch(): BelongsTo
    {
        return $this->belongsTo(self::class, 'next_match_id');
    }

    public function previousMatches(): HasMany
    {
        return $this->hasMany(self::class, 'next_match_id');
    }

    public function isLive(): bool
    {
        return $this->status === MatchStatus::InProgress;
    }

    public function isFinished(): bool
    {
        return $this->status === MatchStatus::Finished;
    }

    public function getWinnerTeamId(): ?int
    {
        if (!$this->isFinished()) {
            return null;
        }

        if ($this->home_score > $this->away_score) {
            return $this->home_team_id;
        }

        if ($this->away_score > $this->home_score) {
            return $this->away_team_id;
        }

        return null; // Draw
    }
}
