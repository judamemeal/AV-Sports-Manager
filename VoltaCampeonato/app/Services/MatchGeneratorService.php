<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Group;
use App\Models\Phase;
use App\Models\Round;
use Carbon\Carbon;

class MatchGeneratorService
{
    /**
     * Generate round-robin matches for a group.
     */
    public function generateRoundRobin(
        Group $group,
        int $championshipId,
        int $phaseId,
        bool $roundTrip = false,
        ?string $startDate = null,
        ?string $matchTime = null,
        array $venues = [],
    ): array {
        $teamIds = $group->teams()->pluck('teams.id')->toArray();
        $numTeams = count($teamIds);

        if ($numTeams < 2) {
            return [];
        }

        // If odd number of teams, add a "bye" (null)
        if ($numTeams % 2 !== 0) {
            $teamIds[] = null;
            $numTeams++;
        }

        $rounds = $numTeams - 1;
        $matchesPerRound = $numTeams / 2;
        $allMatches = [];
        $date = $startDate ? Carbon::parse($startDate) : Carbon::now()->addDays(7);
        $venueIndex = 0;

        // First leg
        for ($roundNum = 0; $roundNum < $rounds; $roundNum++) {
            $round = Round::create([
                'phase_id' => $phaseId,
                'name' => 'Jornada ' . ($roundNum + 1),
                'round_number' => $roundNum + 1,
                'date' => $date->format('Y-m-d'),
            ]);

            for ($matchNum = 0; $matchNum < $matchesPerRound; $matchNum++) {
                $home = $teamIds[$matchNum];
                $away = $teamIds[$numTeams - 1 - $matchNum];

                if ($home === null || $away === null) {
                    continue; // Bye match
                }

                $match = GameMatch::create([
                    'championship_id' => $championshipId,
                    'phase_id' => $phaseId,
                    'group_id' => $group->id,
                    'round_id' => $round->id,
                    'home_team_id' => $home,
                    'away_team_id' => $away,
                    'match_date' => $date->format('Y-m-d'),
                    'match_time' => $matchTime ?? '10:00',
                    'venue' => $venues[$venueIndex % max(1, count($venues))] ?? null,
                    'status' => 'scheduled',
                ]);

                $allMatches[] = $match;
                $venueIndex++;
            }

            // Rotate teams (keep first fixed, rotate the rest)
            $last = array_pop($teamIds);
            array_splice($teamIds, 1, 0, [$last]);

            $date = $date->addDays(7);
        }

        // Second leg (ida y vuelta)
        if ($roundTrip) {
            // Reset team order
            $teamIds = $group->teams()->pluck('teams.id')->toArray();
            if (count($teamIds) % 2 !== 0) {
                $teamIds[] = null;
            }

            for ($roundNum = 0; $roundNum < $rounds; $roundNum++) {
                $round = Round::create([
                    'phase_id' => $phaseId,
                    'name' => 'Jornada ' . ($rounds + $roundNum + 1),
                    'round_number' => $rounds + $roundNum + 1,
                    'date' => $date->format('Y-m-d'),
                ]);

                for ($matchNum = 0; $matchNum < $matchesPerRound; $matchNum++) {
                    $away = $teamIds[$matchNum];
                    $home = $teamIds[$numTeams - 1 - $matchNum];

                    if ($home === null || $away === null) {
                        continue;
                    }

                    $match = GameMatch::create([
                        'championship_id' => $championshipId,
                        'phase_id' => $phaseId,
                        'group_id' => $group->id,
                        'round_id' => $round->id,
                        'home_team_id' => $home,
                        'away_team_id' => $away,
                        'match_date' => $date->format('Y-m-d'),
                        'match_time' => $matchTime ?? '10:00',
                        'venue' => $venues[$venueIndex % max(1, count($venues))] ?? null,
                        'status' => 'scheduled',
                    ]);

                    $allMatches[] = $match;
                    $venueIndex++;
                }

                $last = array_pop($teamIds);
                array_splice($teamIds, 1, 0, [$last]);

                $date = $date->addDays(7);
            }
        }

        return $allMatches;
    }

    /**
     * Generate round-robin for a league (all teams, no group).
     */
    public function generateLeague(
        array $teamIds,
        int $championshipId,
        int $phaseId,
        bool $roundTrip = false,
        ?string $startDate = null,
        ?string $matchTime = null,
        array $venues = [],
    ): array {
        // Create a temporary group-like structure for the league
        $group = Group::create([
            'phase_id' => $phaseId,
            'name' => 'Liga',
            'qualified_count' => count($teamIds),
        ]);

        foreach ($teamIds as $index => $teamId) {
            $group->teams()->attach($teamId, ['seed_position' => $index + 1]);
        }

        return $this->generateRoundRobin(
            $group,
            $championshipId,
            $phaseId,
            $roundTrip,
            $startDate,
            $matchTime,
            $venues,
        );
    }
}
