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
     */
    public function seedTeams(array $matches, array $teamIds): void
    {
        // Sort matches by bracket_position
        usort($matches, fn ($a, $b) => $a->bracket_position - $b->bracket_position);

        $matchIndex = 0;
        
        // Pad the teamIds array with nulls to reach the power of 2 size
        $powerOfTwo = count($matches) * 2;
        while (count($teamIds) < $powerOfTwo) {
            $teamIds[] = null;
        }

        for ($i = 0; $i < $powerOfTwo; $i += 2) {
            if (isset($matches[$matchIndex])) {
                $homeId = $teamIds[$i] ?? null;
                $awayId = $teamIds[$i + 1] ?? null;

                $match = $matches[$matchIndex];
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
            $matchIndex++;
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
