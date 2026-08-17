<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Phase;
use App\Models\Round;
use Carbon\Carbon;

class BracketGeneratorService
{
    /**
     * Generate knockout bracket matches.
     */
    public function generateBracket(
        int $championshipId,
        int $phaseId,
        int $teamCount,
        ?string $startDate = null,
        ?string $matchTime = null,
        array $venues = [],
    ): array {
        $rounds = $this->calculateRounds($teamCount);
        $allMatches = [];
        $date = $startDate ? Carbon::parse($startDate) : Carbon::now()->addDays(7);

        $roundNames = $this->getRoundNames($teamCount);
        $matchesInRound = $teamCount / 2;
        $previousRoundMatches = [];

        foreach ($roundNames as $roundIndex => $roundName) {
            $round = Round::create([
                'phase_id' => $phaseId,
                'name' => $roundName,
                'round_number' => $roundIndex + 1,
                'date' => $date->format('Y-m-d'),
            ]);

            $currentRoundMatches = [];

            for ($i = 0; $i < $matchesInRound; $i++) {
                $match = GameMatch::create([
                    'championship_id' => $championshipId,
                    'phase_id' => $phaseId,
                    'round_id' => $round->id,
                    'match_date' => $date->format('Y-m-d'),
                    'match_time' => $matchTime ?? '10:00',
                    'venue' => $venues[$i % max(1, count($venues))] ?? null,
                    'status' => 'scheduled',
                    'bracket_position' => $i + 1,
                ]);

                $currentRoundMatches[] = $match;
                $allMatches[] = $match;
            }

            // Link previous round matches to this round's matches
            if (!empty($previousRoundMatches)) {
                foreach ($previousRoundMatches as $idx => $prevMatch) {
                    $nextMatchIndex = intdiv($idx, 2);
                    if (isset($currentRoundMatches[$nextMatchIndex])) {
                        $prevMatch->update([
                            'next_match_id' => $currentRoundMatches[$nextMatchIndex]->id,
                        ]);
                    }
                }
            }

            $previousRoundMatches = $currentRoundMatches;
            $matchesInRound = max(1, intdiv($matchesInRound, 2));
            $date = $date->addDays(7);
        }

        return $allMatches;
    }

    /**
     * Seed teams into the first round of a bracket.
     */
    public function seedTeams(array $matches, array $teamIds): void
    {
        // Sort matches by bracket_position
        usort($matches, fn ($a, $b) => $a->bracket_position - $b->bracket_position);

        $matchIndex = 0;
        for ($i = 0; $i < count($teamIds); $i += 2) {
            if (isset($matches[$matchIndex])) {
                $matches[$matchIndex]->update([
                    'home_team_id' => $teamIds[$i] ?? null,
                    'away_team_id' => $teamIds[$i + 1] ?? null,
                ]);
            }
            $matchIndex++;
        }
    }

    /**
     * Calculate number of rounds needed.
     */
    protected function calculateRounds(int $teamCount): int
    {
        return (int) ceil(log($teamCount, 2));
    }

    /**
     * Get round names based on team count.
     */
    protected function getRoundNames(int $teamCount): array
    {
        $names = [];

        if ($teamCount >= 16) $names[] = 'Octavos de Final';
        if ($teamCount >= 8) $names[] = 'Cuartos de Final';
        if ($teamCount >= 4) $names[] = 'Semifinal';
        if ($teamCount >= 2) $names[] = 'Final';

        return $names;
    }
}
