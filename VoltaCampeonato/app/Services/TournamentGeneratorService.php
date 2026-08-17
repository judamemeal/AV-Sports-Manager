<?php

namespace App\Services;

use App\Models\Championship;
use App\Models\Phase;
use App\Models\TournamentFormat;
use Illuminate\Support\Facades\DB;

class TournamentGeneratorService
{
    public function __construct(
        protected GroupGeneratorService $groupGenerator,
        protected MatchGeneratorService $matchGenerator,
        protected BracketGeneratorService $bracketGenerator,
        protected StandingsService $standingsService,
    ) {}

    /**
     * Generate a complete tournament format.
     */
    public function generate(Championship $championship, array $config): TournamentFormat
    {
        return DB::transaction(function () use ($championship, $config) {
            $format = TournamentFormat::create([
                'championship_id' => $championship->id,
                'type' => $config['type'],
                'configuration' => $config,
                'status' => 'generated',
                'is_round_trip' => $config['is_round_trip'] ?? false,
            ]);

            $teamIds = $config['team_ids'];

            match ($config['type']) {
                'league' => $this->generateLeague($format, $championship, $teamIds, $config),
                'groups' => $this->generateGroups($format, $championship, $teamIds, $config),
                'knockout' => $this->generateKnockout($format, $championship, $teamIds, $config),
                'groups_knockout' => $this->generateGroupsKnockout($format, $championship, $teamIds, $config),
                'custom' => $this->generateCustom($format, $championship, $teamIds, $config),
            };

            $format->update(['status' => 'generated']);

            return $format->load('phases.groups.teams');
        });
    }

    /**
     * League format — all vs all.
     */
    protected function generateLeague(TournamentFormat $format, Championship $championship, array $teamIds, array $config): void
    {
        $phase = Phase::create([
            'championship_id' => $championship->id,
            'tournament_format_id' => $format->id,
            'name' => 'Liga',
            'type' => 'league',
            'order' => 1,
            'team_count' => count($teamIds),
        ]);

        $this->matchGenerator->generateLeague(
            $teamIds,
            $championship->id,
            $phase->id,
            $config['is_round_trip'] ?? false,
            $config['start_date'] ?? null,
            $config['match_time'] ?? null,
            $config['venues'] ?? [],
        );
    }

    /**
     * Groups format.
     */
    protected function generateGroups(TournamentFormat $format, Championship $championship, array $teamIds, array $config): void
    {
        $phase = Phase::create([
            'championship_id' => $championship->id,
            'tournament_format_id' => $format->id,
            'name' => 'Fase de Grupos',
            'type' => 'group',
            'order' => 1,
            'team_count' => count($teamIds),
            'configuration' => [
                'groups_count' => $config['groups_count'],
                'teams_per_group' => $config['teams_per_group'],
                'qualified_per_group' => $config['qualified_per_group'],
            ],
        ]);

        $groups = $this->groupGenerator->distributeRandomly($teamIds, $config['groups_count'], $phase->id);

        foreach ($groups as $group) {
            $group->update(['qualified_count' => $config['qualified_per_group']]);

            $groupTeamIds = $group->teams()->pluck('teams.id')->toArray();

            $this->matchGenerator->generateRoundRobin(
                $group,
                $championship->id,
                $phase->id,
                $config['is_round_trip'] ?? false,
                $config['start_date'] ?? null,
                $config['match_time'] ?? null,
                $config['venues'] ?? [],
            );

            $this->standingsService->initializeForGroup(
                $championship->id,
                $group->id,
                $phase->id,
                $groupTeamIds,
            );
        }
    }

    /**
     * Knockout format.
     */
    protected function generateKnockout(TournamentFormat $format, Championship $championship, array $teamIds, array $config): void
    {
        $phase = Phase::create([
            'championship_id' => $championship->id,
            'tournament_format_id' => $format->id,
            'name' => 'Eliminación Directa',
            'type' => 'knockout',
            'order' => 1,
            'team_count' => count($teamIds),
        ]);

        $matches = $this->bracketGenerator->generateBracket(
            $championship->id,
            $phase->id,
            count($teamIds),
            $config['start_date'] ?? null,
            $config['match_time'] ?? null,
            $config['venues'] ?? [],
        );

        // Get only first round matches for seeding
        $firstRoundMatches = array_filter($matches, fn ($m) => $m->bracket_position !== null && !GameMatch::where('next_match_id', $m->id)->exists());

        // If generating directly with known teams, seed them
        shuffle($teamIds);
        $this->bracketGenerator->seedTeams(
            array_values($firstRoundMatches),
            $teamIds,
        );
    }

    /**
     * Groups + Knockout combined format.
     */
    protected function generateGroupsKnockout(TournamentFormat $format, Championship $championship, array $teamIds, array $config): void
    {
        // Phase 1: Groups
        $groupPhase = Phase::create([
            'championship_id' => $championship->id,
            'tournament_format_id' => $format->id,
            'name' => 'Fase de Grupos',
            'type' => 'group',
            'order' => 1,
            'team_count' => count($teamIds),
            'configuration' => [
                'groups_count' => $config['groups_count'],
                'teams_per_group' => $config['teams_per_group'],
                'qualified_per_group' => $config['qualified_per_group'],
            ],
        ]);

        $groups = $this->groupGenerator->distributeRandomly($teamIds, $config['groups_count'], $groupPhase->id);

        foreach ($groups as $group) {
            $group->update(['qualified_count' => $config['qualified_per_group']]);

            $groupTeamIds = $group->teams()->pluck('teams.id')->toArray();

            $this->matchGenerator->generateRoundRobin(
                $group,
                $championship->id,
                $groupPhase->id,
                $config['is_round_trip'] ?? false,
                $config['start_date'] ?? null,
                $config['match_time'] ?? null,
                $config['venues'] ?? [],
            );

            $this->standingsService->initializeForGroup(
                $championship->id,
                $group->id,
                $groupPhase->id,
                $groupTeamIds,
            );
        }

        // Phase 2: Knockout
        $qualifiedCount = $config['groups_count'] * $config['qualified_per_group'];
        $knockoutPhase = Phase::create([
            'championship_id' => $championship->id,
            'tournament_format_id' => $format->id,
            'name' => 'Eliminación Directa',
            'type' => 'knockout',
            'order' => 2,
            'team_count' => $qualifiedCount,
        ]);

        // Generate bracket structure (teams will be seeded after groups finish)
        $this->bracketGenerator->generateBracket(
            $championship->id,
            $knockoutPhase->id,
            $qualifiedCount,
            null,
            $config['match_time'] ?? null,
            $config['venues'] ?? [],
        );
    }

    /**
     * Custom format — multiple user-defined phases.
     */
    protected function generateCustom(TournamentFormat $format, Championship $championship, array $teamIds, array $config): void
    {
        if (empty($config['phases'])) {
            return;
        }

        foreach ($config['phases'] as $order => $phaseConfig) {
            $phase = Phase::create([
                'championship_id' => $championship->id,
                'tournament_format_id' => $format->id,
                'name' => $phaseConfig['name'],
                'type' => $phaseConfig['type'],
                'order' => $order + 1,
                'team_count' => $phaseConfig['team_count'],
                'configuration' => $phaseConfig['configuration'] ?? null,
            ]);

            if ($phaseConfig['type'] === 'group' && $order === 0) {
                $groupsCount = $phaseConfig['configuration']['groups_count'] ?? 2;
                $qualifiedPerGroup = $phaseConfig['configuration']['qualified_per_group'] ?? 2;

                $groups = $this->groupGenerator->distributeRandomly($teamIds, $groupsCount, $phase->id);

                foreach ($groups as $group) {
                    $group->update(['qualified_count' => $qualifiedPerGroup]);
                    $groupTeamIds = $group->teams()->pluck('teams.id')->toArray();

                    $this->matchGenerator->generateRoundRobin(
                        $group,
                        $championship->id,
                        $phase->id,
                        $config['is_round_trip'] ?? false,
                        $config['start_date'] ?? null,
                        $config['match_time'] ?? null,
                        $config['venues'] ?? [],
                    );

                    $this->standingsService->initializeForGroup(
                        $championship->id,
                        $group->id,
                        $phase->id,
                        $groupTeamIds,
                    );
                }
            } elseif (in_array($phaseConfig['type'], ['knockout', 'final', 'play_in'])) {
                $this->bracketGenerator->generateBracket(
                    $championship->id,
                    $phase->id,
                    $phaseConfig['team_count'],
                    null,
                    $config['match_time'] ?? null,
                    $config['venues'] ?? [],
                );
            }
        }
    }

    /**
     * Validate a tournament configuration.
     */
    public function validate(array $config): array
    {
        $errors = [];
        $teamCount = count($config['team_ids'] ?? []);

        if ($teamCount < 2) {
            $errors[] = 'Se necesitan al menos 2 equipos.';
        }

        if (in_array($config['type'], ['groups', 'groups_knockout'])) {
            $groupsCount = $config['groups_count'] ?? 0;
            $teamsPerGroup = $config['teams_per_group'] ?? 0;
            $qualifiedPerGroup = $config['qualified_per_group'] ?? 0;

            $needed = $groupsCount * $teamsPerGroup;
            if ($needed !== $teamCount) {
                $errors[] = "El formato necesita {$needed} equipos ({$groupsCount} grupos × {$teamsPerGroup} equipos), pero hay {$teamCount} disponibles.";
            }

            if ($qualifiedPerGroup >= $teamsPerGroup) {
                $errors[] = "Los clasificados por grupo ({$qualifiedPerGroup}) deben ser menos que los equipos por grupo ({$teamsPerGroup}).";
            }

            $totalQualified = $groupsCount * $qualifiedPerGroup;
            $validKnockoutSizes = [2, 4, 8, 16, 32];
            if ($config['type'] === 'groups_knockout' && !in_array($totalQualified, $validKnockoutSizes)) {
                $errors[] = "El total de clasificados ({$totalQualified}) debe ser 2, 4, 8, 16 o 32 para la fase eliminatoria.";
            }
        }

        if ($config['type'] === 'knockout') {
            $validSizes = [2, 4, 8, 16, 32];
            if (!in_array($teamCount, $validSizes)) {
                $errors[] = "Para eliminación directa se necesitan 2, 4, 8, 16 o 32 equipos. Hay {$teamCount}.";
            }
        }

        return $errors;
    }
}
