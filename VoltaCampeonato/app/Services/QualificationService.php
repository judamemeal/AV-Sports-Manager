<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Group;
use App\Models\Phase;
use App\Models\Standing;

class QualificationService
{
    /**
     * Process a match result and advance the winner in knockout brackets.
     */
    public function processMatchResult(GameMatch $match): void
    {
        if (!$match->isFinished()) {
            return;
        }

        // Handle knockout advancement
        if ($match->next_match_id && $match->getWinnerTeamId()) {
            $this->advanceWinner($match);
        }

        // Check if the group phase is complete
        if ($match->group_id) {
            $this->checkGroupCompletion($match);
        }
    }

    /**
     * Advance the winner to the next knockout match.
     */
    protected function advanceWinner(GameMatch $match): void
    {
        $winnerId = $match->getWinnerTeamId();
        $nextMatch = $match->nextMatch;

        if (!$nextMatch || !$winnerId) {
            return;
        }

        // Find the slot for this winner (home or away)
        $previousMatches = GameMatch::where('next_match_id', $nextMatch->id)
            ->orderBy('bracket_position')
            ->get();

        $index = $previousMatches->search(fn ($m) => $m->id === $match->id);

        if ($index === 0 || $index === false) {
            $nextMatch->update(['home_team_id' => $winnerId]);
        } else {
            $nextMatch->update(['away_team_id' => $winnerId]);
        }
    }

    /**
     * Check if all matches in a group are finished and determine qualifiers.
     */
    protected function checkGroupCompletion(GameMatch $match): void
    {
        $group = Group::find($match->group_id);
        if (!$group) return;

        $format = $group->phase->tournamentFormat;
        $isRoundTrip = $format && $format->is_round_trip;

        $totalTeams = $group->teams()->count();
        $totalMatchesNeeded = ($totalTeams * ($totalTeams - 1)) / 2;
        if ($isRoundTrip) {
            $totalMatchesNeeded *= 2;
        }

        $finishedMatches = GameMatch::where('group_id', $group->id)
            ->where('status', 'finished')
            ->count();

        if ($finishedMatches < $totalMatchesNeeded) {
            return; // Group not complete yet
        }

        // Get qualifiers
        $qualifiers = $this->getGroupQualifiers($group);

        // Check if there's a next phase to seed
        $phase = $group->phase;
        $nextPhase = Phase::where('championship_id', $phase->championship_id)
            ->where('order', '>', $phase->order)
            ->orderBy('order')
            ->first();

        if ($nextPhase && $nextPhase->type->value === 'knockout') {
            // Auto-seed qualifiers into knockout if all groups are done
            $this->checkAllGroupsComplete($phase, $nextPhase);
        }
    }

    /**
     * Get the qualified teams from a group ordered by position.
     */
    public function getGroupQualifiers(Group $group): array
    {
        return Standing::where('group_id', $group->id)
            ->orderByDesc('points')
            ->orderByDesc('goal_difference')
            ->orderByDesc('goals_for')
            ->limit($group->qualified_count)
            ->pluck('team_id')
            ->toArray();
    }

    /**
     * Check if all groups in a phase are complete and seed the next phase.
     */
    protected function checkAllGroupsComplete(Phase $groupPhase, Phase $nextPhase): void
    {
        $format = $groupPhase->tournamentFormat;
        $isRoundTrip = $format && $format->is_round_trip;
        
        $allGroups = $groupPhase->groups;
        $allComplete = true;
        $qualifiersByGroup = [];

        foreach ($allGroups as $group) {
            $totalTeams = $group->teams()->count();
            $totalMatchesNeeded = ($totalTeams * ($totalTeams - 1)) / 2;
            if ($isRoundTrip) {
                $totalMatchesNeeded *= 2;
            }

            $finishedMatches = GameMatch::where('group_id', $group->id)
                ->where('status', 'finished')
                ->count();

            if ($finishedMatches < $totalMatchesNeeded) {
                $allComplete = false;
                break;
            }

            $qualifiersByGroup[] = $this->getGroupQualifiers($group);
        }

        if (!$allComplete) {
            return;
        }

        // Interleave qualifiers (cross-seeding)
        $qualifiedTeams = [];
        $groupsCount = count($qualifiersByGroup);
        if ($groupsCount > 0) {
            $positions = count($qualifiersByGroup[0]);
            
            if ($groupsCount === 2 && $positions === 2) {
                // Standard 1A vs 2B, 1B vs 2A
                $qualifiedTeams = [
                    $qualifiersByGroup[0][0] ?? null,
                    $qualifiersByGroup[1][1] ?? null,
                    $qualifiersByGroup[1][0] ?? null,
                    $qualifiersByGroup[0][1] ?? null,
                ];
            } else {
                // Generic interleave: alternate firsts, then alternate seconds, etc.
                for ($p = 0; $p < $positions; $p++) {
                    for ($g = 0; $g < $groupsCount; $g++) {
                        // Alternate order for even positions to mix up matches
                        $groupIndex = ($p % 2 === 0) ? $g : ($groupsCount - 1 - $g);
                        if (isset($qualifiersByGroup[$groupIndex][$p])) {
                            $qualifiedTeams[] = $qualifiersByGroup[$groupIndex][$p];
                        }
                    }
                }
            }
        }

        // Mark group phase as completed
        $groupPhase->update(['is_completed' => true]);

        // Seed qualified teams into knockout bracket
        $knockoutMatches = $nextPhase->matches()
            ->orderBy('bracket_position')
            ->get();

        if ($knockoutMatches->isNotEmpty()) {
            $bracketService = app(BracketGeneratorService::class);
            $bracketService->seedTeams($knockoutMatches->all(), $qualifiedTeams);
        }
    }

    /**
     * Get best third-placed teams across groups.
     */
    public function getBestThirds(Phase $phase, int $count): array
    {
        $thirdPlaced = [];

        foreach ($phase->groups as $group) {
            $third = Standing::where('group_id', $group->id)
                ->orderByDesc('points')
                ->orderByDesc('goal_difference')
                ->orderByDesc('goals_for')
                ->skip(2)
                ->first();

            if ($third) {
                $thirdPlaced[] = $third;
            }
        }

        // Sort thirds by points, GD, GF
        usort($thirdPlaced, function ($a, $b) {
            if ($a->points !== $b->points) return $b->points - $a->points;
            if ($a->goal_difference !== $b->goal_difference) return $b->goal_difference - $a->goal_difference;
            return $b->goals_for - $a->goals_for;
        });

        return array_slice(
            array_map(fn ($s) => $s->team_id, $thirdPlaced),
            0,
            $count
        );
    }
}
