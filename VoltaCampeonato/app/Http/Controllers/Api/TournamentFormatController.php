<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TournamentFormat\GenerateFormatRequest;
use App\Http\Resources\TournamentFormatResource;
use App\Models\Championship;
use App\Models\TournamentFormat;
use App\Services\TournamentGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TournamentFormatController extends Controller
{
    public function __construct(
        protected TournamentGeneratorService $generator,
    ) {}

    public function show(Championship $championship): JsonResponse
    {
        $format = $championship->tournamentFormats()
            ->with('phases.groups.teams', 'phases.groups.standings.team', 'phases.rounds', 'phases.matches.homeTeam', 'phases.matches.awayTeam')
            ->latest()
            ->first();

        if (!$format) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => new TournamentFormatResource($format),
        ]);
    }

    public function generate(GenerateFormatRequest $request, Championship $championship): JsonResponse
    {
        $config = $request->validated();

        // Validate the format configuration
        $errors = $this->generator->validate($config);

        if (!empty($errors)) {
            return response()->json([
                'message' => 'El formato no es válido.',
                'errors' => $errors,
            ], 422);
        }

        $format = $this->generator->generate($championship, $config);

        return response()->json([
            'message' => 'Formato generado correctamente.',
            'data' => new TournamentFormatResource($format),
        ], 201);
    }

    public function validate(Request $request, Championship $championship): JsonResponse
    {
        $config = $request->all();
        $errors = $this->generator->validate($config);

        return response()->json([
            'valid' => empty($errors),
            'errors' => $errors,
        ]);
    }

    public function update(Request $request, TournamentFormat $format): JsonResponse
    {
        $format->update($request->only(['configuration', 'status', 'is_round_trip']));

        return response()->json([
            'message' => 'Formato actualizado.',
            'data' => new TournamentFormatResource($format->load('phases')),
        ]);
    }

    public function updateGroupTeams(Request $request, $groupId): JsonResponse
    {
        $group = \App\Models\Group::findOrFail($groupId);

        $request->validate([
            'team_ids' => ['required', 'array'],
            'team_ids.*' => ['exists:teams,id'],
        ]);

        $group->teams()->sync(
            collect($request->team_ids)->mapWithKeys(fn ($id, $index) => [
                $id => ['seed_position' => $index + 1],
            ])->all()
        );

        return response()->json([
            'message' => 'Equipos del grupo actualizados.',
            'data' => $group->load('teams'),
        ]);
    }
}
