<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'phase_id',
        'name',
        'qualified_count',
    ];

    protected function casts(): array
    {
        return [
            'qualified_count' => 'integer',
        ];
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(Phase::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'group_teams')
            ->withPivot('seed_position')
            ->withTimestamps();
    }

    public function standings(): HasMany
    {
        return $this->hasMany(Standing::class)->orderBy('position');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(GameMatch::class);
    }
}
