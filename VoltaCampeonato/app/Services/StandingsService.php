<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Standing;

class StandingsService
{
    /**
     * Update standings after a match is finished.
     */
    public function updateAfterMatch(GameMatch $match): void
    {
        if (!$match->home_team_id || !$match->away_team_id) {
            return;
        }

        $this->updateTeamStanding($match, $match->home_team_id, true);
        $this->updateTeamStanding($match, $match->away_team_id, false);
        $this->recalculatePositions($match->championship_id, $match->group_id);
    }

    /**
     * Update a single team's standing based on match result.
     */
    protected function updateTeamStanding(GameMatch $match, int $teamId, bool $isHome): void
    {
        $standing = Standing::firstOrCreate(
            [
                'championship_id' => $match->championship_id,
                'group_id' => $match->group_id,
                'team_id' => $teamId,
            ],
            [
                'phase_id' => $match->phase_id,
                'played' => 0,
                'won' => 0,
                'drawn' => 0,
                'lost' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'goal_difference' => 0,
                'points' => 0,
                'position' => 0,
            ]
        );

        $goalsFor = $isHome ? $match->home_score : $match->away_score;
        $goalsAgainst = $isHome ? $match->away_score : $match->home_score;

        $standing->played += 1;
        $standing->goals_for += $goalsFor;
        $standing->goals_against += $goalsAgainst;
        $standing->goal_difference = $standing->goals_for - $standing->goals_against;

        if ($goalsFor > $goalsAgainst) {
            $standing->won += 1;
            $standing->points += 3;
        } elseif ($goalsFor === $goalsAgainst) {
            $standing->drawn += 1;
            $standing->points += 1;
        } else {
            $standing->lost += 1;
        }

        $standing->save();
    }

    /**
     * Recalculate positions for all teams in a championship/group.
     */
    public function recalculatePositions(int $championshipId, ?int $groupId = null): void
    {
        $query = Standing::where('championship_id', $championshipId);

        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        $standings = $query
            ->orderByDesc('points')
            ->orderByDesc('goal_difference')
            ->orderByDesc('goals_for')
            ->get();

        $position = 1;
        foreach ($standings as $standing) {
            $standing->update(['position' => $position++]);
        }
    }

    /**
     * Initialize standings for teams in a group.
     */
    public function initializeForGroup(int $championshipId, int $groupId, int $phaseId, array $teamIds): void
    {
        foreach ($teamIds as $teamId) {
            Standing::firstOrCreate(
                [
                    'championship_id' => $championshipId,
                    'group_id' => $groupId,
                    'team_id' => $teamId,
                ],
                [
                    'phase_id' => $phaseId,
                    'played' => 0,
                    'won' => 0,
                    'drawn' => 0,
                    'lost' => 0,
                    'goals_for' => 0,
                    'goals_against' => 0,
                    'goal_difference' => 0,
                    'points' => 0,
                    'position' => 0,
                ]
            );
        }
    }
}
