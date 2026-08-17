<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Team;
use Illuminate\Support\Collection;

class GroupGeneratorService
{
    /**
     * Distribute teams into groups randomly.
     */
    public function distributeRandomly(array $teamIds, int $groupCount, int $phaseId): array
    {
        $shuffled = collect($teamIds)->shuffle();
        $groups = [];

        for ($i = 0; $i < $groupCount; $i++) {
            $groupName = 'Grupo ' . chr(65 + $i); // A, B, C, D...
            $group = Group::create([
                'phase_id' => $phaseId,
                'name' => $groupName,
                'qualified_count' => 2,
            ]);

            $groups[] = $group;
        }

        // Distribute teams round-robin style into groups
        $groupIndex = 0;
        foreach ($shuffled as $seed => $teamId) {
            $groups[$groupIndex]->teams()->attach($teamId, [
                'seed_position' => $seed + 1,
            ]);

            $groupIndex = ($groupIndex + 1) % $groupCount;
        }

        return $groups;
    }

    /**
     * Manually assign teams to groups.
     */
    public function distributeManually(array $groupAssignments, int $phaseId): array
    {
        $groups = [];

        foreach ($groupAssignments as $index => $assignment) {
            $groupName = $assignment['name'] ?? 'Grupo ' . chr(65 + $index);
            $qualifiedCount = $assignment['qualified_count'] ?? 2;

            $group = Group::create([
                'phase_id' => $phaseId,
                'name' => $groupName,
                'qualified_count' => $qualifiedCount,
            ]);

            foreach ($assignment['team_ids'] as $seed => $teamId) {
                $group->teams()->attach($teamId, [
                    'seed_position' => $seed + 1,
                ]);
            }

            $groups[] = $group;
        }

        return $groups;
    }

    /**
     * Validate that team distribution is valid.
     */
    public function validateDistribution(array $teamIds, int $groupCount, int $teamsPerGroup): array
    {
        $errors = [];
        $totalNeeded = $groupCount * $teamsPerGroup;
        $totalAvailable = count($teamIds);

        if ($totalAvailable !== $totalNeeded) {
            $errors[] = "Se necesitan {$totalNeeded} equipos ({$groupCount} grupos × {$teamsPerGroup} equipos), pero hay {$totalAvailable} disponibles.";
        }

        if (count($teamIds) !== count(array_unique($teamIds))) {
            $errors[] = "Hay equipos duplicados en la selección.";
        }

        return $errors;
    }
}
