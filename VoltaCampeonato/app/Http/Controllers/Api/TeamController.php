<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Http\Resources\MatchResource;
use App\Http\Resources\PlayerResource;
use App\Http\Resources\TeamResource;
use App\Models\GameMatch;
use App\Models\Standing;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeamController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Team::with('championships:id,name')->withCount('players');

        if ($request->filled('championship_id')) {
            $query->whereHas('championships', function ($q) use ($request) {
                $q->where('championships.id', $request->championship_id);
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $teams = $query->orderBy('name')->paginate(20);

        return TeamResource::collection($teams);
    }

    public function store(StoreTeamRequest $request): JsonResponse
    {
        $data = $request->validated();
        $championshipIds = $data['championship_ids'] ?? [];
        unset($data['championship_ids']);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('team-logos', 'public');
        }

        $team = Team::create($data);

        if (!empty($championshipIds)) {
            $team->championships()->sync($championshipIds);
        }

        return response()->json(new TeamResource($team->load('championships')), 201);
    }

    public function show(Team $team): TeamResource
    {
        $team->load(['championships:id,name', 'players', 'standings']);
        $team->loadCount('players');

        return new TeamResource($team);
    }

    public function update(UpdateTeamRequest $request, Team $team): TeamResource
    {
        $data = $request->validated();
        $championshipIds = $data['championship_ids'] ?? null;
        unset($data['championship_ids']);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('team-logos', 'public');
        }

        $team->update($data);

        if (is_array($championshipIds)) {
            $team->championships()->sync($championshipIds);
        }

        return new TeamResource($team->load('championships'));
    }

    public function destroy(Team $team): JsonResponse
    {
        $team->delete();

        return response()->json(['message' => 'Equipo eliminado correctamente.']);
    }

    public function players(Team $team): AnonymousResourceCollection
    {
        $players = $team->players()
            ->withCount(['goals', 'yellowCards', 'redCards'])
            ->orderBy('jersey_number')
            ->get();

        return PlayerResource::collection($players);
    }

    public function matches(Team $team): AnonymousResourceCollection
    {
        $matches = GameMatch::where('home_team_id', $team->id)
            ->orWhere('away_team_id', $team->id)
            ->with(['homeTeam', 'awayTeam', 'phase', 'round'])
            ->orderByDesc('match_date')
            ->paginate(15);

        return MatchResource::collection($matches);
    }

    public function statistics(Team $team): JsonResponse
    {
        $standing = Standing::where('team_id', $team->id)
            ->where('championship_id', $team->championship_id)
            ->whereNull('group_id')
            ->first();

        if (!$standing) {
            $standing = Standing::where('team_id', $team->id)
                ->where('championship_id', $team->championship_id)
                ->first();
        }

        return response()->json([
            'data' => $standing ? [
                'played' => $standing->played,
                'won' => $standing->won,
                'drawn' => $standing->drawn,
                'lost' => $standing->lost,
                'goals_for' => $standing->goals_for,
                'goals_against' => $standing->goals_against,
                'goal_difference' => $standing->goal_difference,
                'points' => $standing->points,
            ] : null,
        ]);
    }
}
