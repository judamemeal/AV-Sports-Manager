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
        $powerOfTwo = $this->calculatePowerOfTwo($teamCount);
        $rounds = (int) log($powerOfTwo, 2);
        $allMatches = [];
        $date = $startDate ? Carbon::parse($startDate) : Carbon::now()->addDays(7);

        $roundNames = $this->getRoundNames($powerOfTwo);
        $matchesInRound = $powerOfTwo / 2;
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
     * Expects $teamIds to be ordered by strength/ranking if applicable.
     */
    public function seedTeams(array $matches, array $teamIds): void
    {
        usort($matches, fn ($a, $b) => $a->bracket_position - $b->bracket_position);
        
        $powerOfTwo = count($matches) * 2;
        $teamCount = count($teamIds);
        
        // Calculate how many byes we need
        $byesCount = $powerOfTwo - $teamCount;
        
        // Create an array of pairs
        $pairs = array_fill(0, count($matches), [null, null]);
        
        // 1. Give byes to the top seeds (first elements in array)
        // 2. Distribute remaining teams.
        // We will distribute the top teams to the 'home' slots.
        $topSeeds = array_slice($teamIds, 0, count($matches));
        $bottomSeeds = array_slice($teamIds, count($matches));
        
        // Standard Tennis/FIFA seeding logic:
        // 1 goes to match 1, 2 goes to match N, 3 goes to match N/2...
        // For simplicity, we just distribute them sequentially to home, then fill away from the bottom.
        
        for ($i = 0; $i < count($matches); $i++) {
            $pairs[$i][0] = $topSeeds[$i] ?? null;
        }
        
        // Fill the away slots with the bottom seeds, reversed so top seed plays the lowest seed
        $bottomSeedsReversed = array_reverse($bottomSeeds);
        for ($i = 0; $i < count($matches); $i++) {
            $pairs[$i][1] = $bottomSeedsReversed[$i] ?? null;
        }

        // Now save to database
        foreach ($matches as $index => $match) {
            $homeId = $pairs[$index][0];
            $awayId = $pairs[$index][1];

            $match->update([
                'home_team_id' => $homeId,
                'away_team_id' => $awayId,
            ]);

            // Auto-advance if there is a Bye
            if ($homeId !== null && $awayId === null) {
                $match->update(['status' => 'finished', 'home_score' => 1, 'away_score' => 0]);
                app(QualificationService::class)->processMatchResult($match);
            } elseif ($homeId === null && $awayId !== null) {
                $match->update(['status' => 'finished', 'home_score' => 0, 'away_score' => 1]);
                app(QualificationService::class)->processMatchResult($match);
            }
        }
    }

    /**
     * Calculate the next power of 2.
     */
    protected function calculatePowerOfTwo(int $teamCount): int
    {
        if ($teamCount < 2) return 2;
        return pow(2, ceil(log($teamCount, 2)));
    }

    /**
     * Get round names based on power of 2 team count.
     */
    protected function getRoundNames(int $powerOfTwo): array
    {
        $names = [];
        $currentPower = $powerOfTwo;

        while ($currentPower >= 2) {
            if ($currentPower === 2) {
                $names[] = 'Final';
            } elseif ($currentPower === 4) {
                $names[] = 'Semifinal';
            } elseif ($currentPower === 8) {
                $names[] = 'Cuartos de Final';
            } elseif ($currentPower === 16) {
                $names[] = 'Octavos de Final';
            } else {
                $names[] = 'Ronda de ' . $currentPower;
            }
            $currentPower /= 2;
        }

        return $names;
    }
}
