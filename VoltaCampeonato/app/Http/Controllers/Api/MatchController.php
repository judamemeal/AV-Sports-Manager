<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Match\StoreMatchEventRequest;
use App\Http\Requests\Match\StoreMatchRequest;
use App\Http\Requests\Match\UpdateMatchRequest;
use App\Http\Resources\MatchEventResource;
use App\Http\Resources\MatchResource;
use App\Models\GameMatch;
use App\Models\MatchEvent;
use App\Services\StandingsService;
use App\Services\QualificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MatchController extends Controller
{
    public function __construct(
        protected StandingsService $standingsService,
        protected QualificationService $qualificationService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = GameMatch::with(['homeTeam', 'awayTeam', 'phase', 'round', 'championship:id,name'])
            ->whereHas('championship');

        if ($request->filled('championship_id')) {
            $query->where('championship_id', $request->championship_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('phase_id')) {
            $query->where('phase_id', $request->phase_id);
        }

        if ($request->filled('team_id')) {
            $teamId = $request->team_id;
            $query->where(fn ($q) => $q->where('home_team_id', $teamId)->orWhere('away_team_id', $teamId));
        }

        if ($request->filled('date')) {
            $query->whereDate('match_date', $request->date);
        }

        $matches = $query->orderBy('match_date')->orderBy('match_time')->paginate(20);

        return MatchResource::collection($matches);
    }

    public function store(StoreMatchRequest $request): JsonResponse
    {
        $match = GameMatch::create($request->validated());

        return response()->json(
            new MatchResource($match->load(['homeTeam', 'awayTeam'])),
            201
        );
    }

    public function show(GameMatch $match): MatchResource
    {
        $match->load([
            'homeTeam.players',
            'awayTeam.players',
            'events.player',
            'events.team',
            'phase',
            'group',
            'round',
            'championship:id,name',
        ]);

        return new MatchResource($match);
    }

    public function update(UpdateMatchRequest $request, GameMatch $match): MatchResource
    {
        $match->update($request->validated());

        return new MatchResource($match->load(['homeTeam.players', 'awayTeam.players']));
    }

    public function destroy(GameMatch $match): JsonResponse
    {
        $match->delete();

        return response()->json(['message' => 'Partido eliminado correctamente.']);
    }

    /**
     * Start a match — change status to in_progress.
     */
    public function start(GameMatch $match): JsonResponse
    {
        if ($match->status->value !== 'scheduled') {
            return response()->json(['message' => 'Solo se pueden iniciar partidos programados.'], 422);
        }

        $match->update([
            'status' => 'in_progress',
            'home_score' => 0,
            'away_score' => 0,
        ]);

        return response()->json([
            'message' => 'Partido iniciado.',
            'data' => new MatchResource($match->load(['homeTeam.players', 'awayTeam.players', 'events'])),
        ]);
    }

    /**
     * Record a match event (goal, card, substitution).
     */
    public function recordEvent(StoreMatchEventRequest $request, GameMatch $match): JsonResponse
    {
        if ($match->status->value !== 'in_progress') {
            return response()->json(['message' => 'Solo se pueden registrar eventos en partidos en juego.'], 422);
        }

        if ($match->home_team_id != $request->team_id && $match->away_team_id != $request->team_id) {
            return response()->json(['message' => 'El equipo no participa en este partido.'], 422);
        }

        if ($request->filled('player_id')) {
            $player = \App\Models\Player::find($request->player_id);
            if ($player && $player->team_id != $request->team_id) {
                return response()->json(['message' => 'El jugador no pertenece al equipo seleccionado.'], 422);
            }
        }

        $event = $match->events()->create($request->validated());

        // If it's a goal, update the score
        if ($request->type === 'goal') {
            if ($request->team_id == $match->home_team_id) {
                $match->increment('home_score');
            } else {
                $match->increment('away_score');
            }
            $match->refresh();
        }

        $event->load(['player', 'team']);

        return response()->json([
            'message' => 'Evento registrado.',
            'event' => new MatchEventResource($event),
            'score' => [
                'home' => $match->home_score,
                'away' => $match->away_score,
            ],
        ]);
    }

    /**
     * Delete a match event (undo).
     */
    public function deleteEvent(GameMatch $match, MatchEvent $event): JsonResponse
    {
        if ($match->status->value !== 'in_progress') {
            return response()->json(['message' => 'Solo se pueden eliminar eventos en partidos en juego.'], 422);
        }

        if ($event->game_match_id !== $match->id) {
            return response()->json(['message' => 'El evento no pertenece a este partido.'], 422);
        }

        if ($event->type === 'goal') {
            if ($event->team_id == $match->home_team_id && $match->home_score > 0) {
                $match->decrement('home_score');
            } elseif ($event->team_id == $match->away_team_id && $match->away_score > 0) {
                $match->decrement('away_score');
            }
        }

        $event->delete();
        $match->refresh();

        return response()->json([
            'message' => 'Evento eliminado.',
            'score' => [
                'home' => $match->home_score,
                'away' => $match->away_score,
            ],
        ]);
    }

    /**
     * Finish a match — save result and update standings.
     */
    public function finish(Request $request, GameMatch $match): JsonResponse
    {
        if ($match->status->value !== 'in_progress' && $match->status->value !== 'scheduled') {
            return response()->json(['message' => 'El partido no se puede finalizar en su estado actual.'], 422);
        }

        $data = ['status' => 'finished'];
        
        if ($request->has('home_score')) {
            $data['home_score'] = $request->input('home_score');
        }
        
        if ($request->has('away_score')) {
            $data['away_score'] = $request->input('away_score');
        }

        $match->update($data);

        // Update standings
        $this->standingsService->updateAfterMatch($match);

        // Check if we need to advance teams in knockout
        $this->qualificationService->processMatchResult($match);

        $match->load(['homeTeam.players', 'awayTeam.players', 'events.player']);

        return response()->json([
            'message' => 'Partido finalizado. Resultados actualizados.',
            'data' => new MatchResource($match),
        ]);
    }

    /**
     * Get events for a match.
     */
    public function events(GameMatch $match): AnonymousResourceCollection
    {
        $events = $match->events()
            ->with(['player', 'team'])
            ->orderBy('minute')
            ->get();

        return MatchEventResource::collection($events);
    }

    /**
     * Get live matches.
     */
    public function live(): AnonymousResourceCollection
    {
        $matches = GameMatch::where('status', 'in_progress')
            ->whereHas('championship')
            ->with(['homeTeam', 'awayTeam', 'championship:id,name', 'events.player', 'round', 'phase'])
            ->get();

        return MatchResource::collection($matches);
    }
}
