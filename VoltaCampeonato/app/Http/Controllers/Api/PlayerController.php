<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Player\StorePlayerRequest;
use App\Http\Requests\Player\UpdatePlayerRequest;
use App\Http\Resources\PlayerResource;
use App\Models\MatchEvent;
use App\Models\Player;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlayerController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Player::with('team:id,name,color,championship_id')
            ->withCount(['goals', 'yellowCards', 'redCards']);

        if ($request->filled('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) =>
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
            );
        }

        $players = $query->orderBy('last_name')->paginate(20);

        return PlayerResource::collection($players);
    }

    public function store(StorePlayerRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('player-photos', 'public');
        }

        $player = Player::create($data);

        return response()->json(new PlayerResource($player->load('team:id,name,color')), 201);
    }

    public function show(Player $player): PlayerResource
    {
        $player->load('team:id,name,color,championship_id');
        $player->loadCount(['goals', 'yellowCards', 'redCards']);

        return new PlayerResource($player);
    }

    public function update(UpdatePlayerRequest $request, Player $player): PlayerResource
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('player-photos', 'public');
        }

        $player->update($data);

        return new PlayerResource($player->load('team:id,name,color'));
    }

    public function destroy(Player $player): JsonResponse
    {
        $player->delete();

        return response()->json(['message' => 'Jugador eliminado correctamente.']);
    }

    public function statistics(Player $player): JsonResponse
    {
        $matchIds = MatchEvent::where('player_id', $player->id)->pluck('game_match_id')->unique();

        return response()->json([
            'data' => [
                'matches_played' => $matchIds->count(),
                'goals' => $player->goals()->count(),
                'yellow_cards' => $player->yellowCards()->count(),
                'red_cards' => $player->redCards()->count(),
                'recent_events' => $player->matchEvents()
                    ->with(['gameMatch.homeTeam:id,name', 'gameMatch.awayTeam:id,name'])
                    ->orderByDesc('created_at')
                    ->limit(10)
                    ->get()
                    ->map(fn ($e) => [
                        'type' => $e->type->value,
                        'type_icon' => $e->type->icon(),
                        'minute' => $e->minute,
                        'match' => $e->gameMatch ? [
                            'id' => $e->gameMatch->id,
                            'home_team' => $e->gameMatch->homeTeam?->name,
                            'away_team' => $e->gameMatch->awayTeam?->name,
                            'date' => $e->gameMatch->match_date?->format('Y-m-d'),
                        ] : null,
                    ]),
            ],
        ]);
    }
}
