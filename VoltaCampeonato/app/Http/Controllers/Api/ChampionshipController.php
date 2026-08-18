<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Championship\StoreChampionshipRequest;
use App\Http\Requests\Championship\UpdateChampionshipRequest;
use App\Http\Resources\ChampionshipResource;
use App\Http\Resources\MatchResource;
use App\Http\Resources\StandingResource;
use App\Models\Championship;
use App\Models\GameMatch;
use App\Models\MatchEvent;
use App\Models\Player;
use App\Models\Standing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChampionshipController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Championship::withCount(['teams', 'matches']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('sport')) {
            $query->where('sport', $request->sport);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $championships = $query->orderByDesc('year')->orderByDesc('created_at')->paginate(15);

        return ChampionshipResource::collection($championships);
    }

    public function store(StoreChampionshipRequest $request): JsonResponse
    {
        $championship = Championship::create($request->validated());

        return response()->json(
            new ChampionshipResource($championship),
            201
        );
    }

    public function show(Championship $championship): ChampionshipResource
    {
        $championship->load(['teams.players', 'phases.groups.teams']);
        $championship->loadCount(['teams', 'matches']);

        return new ChampionshipResource($championship);
    }

    public function update(UpdateChampionshipRequest $request, Championship $championship): ChampionshipResource
    {
        $championship->update($request->validated());

        return new ChampionshipResource($championship);
    }

    public function destroy(Championship $championship): JsonResponse
    {
        $championship->delete();

        return response()->json(['message' => 'Campeonato eliminado correctamente.']);
    }

    public function standings(Championship $championship): AnonymousResourceCollection
    {
        $standings = Standing::where('championship_id', $championship->id)
            ->with('team', 'group')
            ->orderByDesc('points')
            ->orderByDesc('goal_difference')
            ->orderByDesc('goals_for')
            ->get();

        return StandingResource::collection($standings);
    }

    public function scorers(Championship $championship): JsonResponse
    {
        $teamIds = $championship->teams()->pluck('id');

        $scorers = Player::whereIn('team_id', $teamIds)
            ->withCount('goals')
            ->with('team:id,name,color')
            ->get()
            ->filter(fn ($player) => $player->goals_count > 0)
            ->sortByDesc('goals_count')
            ->take(20)
            ->values()
            ->map(fn ($player) => [
                'id' => $player->id,
                'full_name' => $player->full_name,
                'jersey_number' => $player->jersey_number,
                'team' => $player->team ? [
                    'id' => $player->team->id,
                    'name' => $player->team->name,
                    'color' => $player->team->color,
                ] : null,
                'goals' => $player->goals_count,
            ]);

        return response()->json(['data' => $scorers]);
    }

    public function statistics(Championship $championship): JsonResponse
    {
        $teamIds = $championship->teams()->pluck('id');
        $matchIds = $championship->matches()->pluck('id');

        $totalMatches = $championship->matches()->where('status', 'finished')->count();
        $totalGoals = MatchEvent::whereIn('game_match_id', $matchIds)->where('type', 'goal')->count();
        $totalYellowCards = MatchEvent::whereIn('game_match_id', $matchIds)->where('type', 'yellow_card')->count();
        $totalRedCards = MatchEvent::whereIn('game_match_id', $matchIds)->where('type', 'red_card')->count();

        return response()->json([
            'data' => [
                'total_teams' => $championship->teams()->count(),
                'total_players' => Player::whereIn('team_id', $teamIds)->count(),
                'total_matches' => $totalMatches,
                'total_goals' => $totalGoals,
                'total_yellow_cards' => $totalYellowCards,
                'total_red_cards' => $totalRedCards,
                'avg_goals_per_match' => $totalMatches > 0 ? round($totalGoals / $totalMatches, 2) : 0,
            ],
        ]);
    }

    public function calendar(Championship $championship, Request $request): AnonymousResourceCollection
    {
        $query = $championship->matches()->with(['homeTeam', 'awayTeam', 'phase', 'group', 'round']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('team_id')) {
            $teamId = $request->team_id;
            $query->where(fn ($q) => $q->where('home_team_id', $teamId)->orWhere('away_team_id', $teamId));
        }

        if ($request->filled('phase_id')) {
            $query->where('phase_id', $request->phase_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('match_date', $request->date);
        }

        $matches = $query->orderBy('match_date')->orderBy('match_time')->paginate(20);

        return MatchResource::collection($matches);
    }

    public function phases(Championship $championship)
    {
        $phases = $championship->phases()
            ->with(['groups.teams', 'groups.standings.team', 'matches.homeTeam', 'matches.awayTeam'])
            ->orderBy('order')
            ->get();

        return response()->json(['data' => $phases]);
    }

    public function brackets(Championship $championship)
    {
        $knockoutPhases = $championship->phases()
            ->whereIn('type', ['knockout', 'final', 'play_in'])
            ->with(['matches.homeTeam', 'matches.awayTeam', 'matches.nextMatch'])
            ->orderBy('order')
            ->get();

        return response()->json(['data' => $knockoutPhases]);
    }
}
