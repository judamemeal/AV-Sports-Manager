<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Championship;
use App\Models\GameMatch;
use App\Models\MatchEvent;
use App\Models\Player;
use App\Models\Standing;
use App\Models\Team;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $activeChampionships = Championship::where('status', 'active')->count();
        $totalTeams = Team::count();
        $totalPlayers = Player::count();
        $matchesPlayed = GameMatch::where('status', 'finished')->count();
        $matchesPending = GameMatch::where('status', 'scheduled')->count();
        $totalGoals = MatchEvent::where('type', 'goal')->count();

        // Top team (most points)
        $topTeam = Standing::with('team:id,name,color')
            ->orderByDesc('points')
            ->orderByDesc('goal_difference')
            ->first();

        // Top scorer
        $topScorer = Player::withCount('goals')
            ->having('goals_count', '>', 0)
            ->with('team:id,name,color')
            ->orderByDesc('goals_count')
            ->first();

        // Upcoming matches
        $upcomingMatches = GameMatch::where('status', 'scheduled')
            ->with(['homeTeam:id,name,color', 'awayTeam:id,name,color', 'championship:id,name'])
            ->orderBy('match_date')
            ->orderBy('match_time')
            ->limit(5)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'match_date' => $m->match_date?->format('Y-m-d'),
                'match_time' => $m->match_time,
                'venue' => $m->venue,
                'home_team' => $m->homeTeam ? ['id' => $m->homeTeam->id, 'name' => $m->homeTeam->name, 'color' => $m->homeTeam->color] : null,
                'away_team' => $m->awayTeam ? ['id' => $m->awayTeam->id, 'name' => $m->awayTeam->name, 'color' => $m->awayTeam->color] : null,
                'championship' => $m->championship?->name,
            ]);

        // Live matches
        $liveMatches = GameMatch::where('status', 'in_progress')
            ->with(['homeTeam:id,name,color', 'awayTeam:id,name,color'])
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'home_team' => $m->homeTeam ? ['id' => $m->homeTeam->id, 'name' => $m->homeTeam->name, 'color' => $m->homeTeam->color] : null,
                'away_team' => $m->awayTeam ? ['id' => $m->awayTeam->id, 'name' => $m->awayTeam->name, 'color' => $m->awayTeam->color] : null,
                'home_score' => $m->home_score,
                'away_score' => $m->away_score,
            ]);

        return response()->json([
            'data' => [
                'active_championships' => $activeChampionships,
                'total_teams' => $totalTeams,
                'total_players' => $totalPlayers,
                'matches_played' => $matchesPlayed,
                'matches_pending' => $matchesPending,
                'total_goals' => $totalGoals,
                'top_team' => $topTeam ? [
                    'name' => $topTeam->team?->name,
                    'color' => $topTeam->team?->color,
                    'points' => $topTeam->points,
                ] : null,
                'top_scorer' => $topScorer ? [
                    'name' => $topScorer->full_name,
                    'team' => $topScorer->team?->name,
                    'goals' => $topScorer->goals_count,
                ] : null,
                'upcoming_matches' => $upcomingMatches,
                'live_matches' => $liveMatches,
            ],
        ]);
    }
}
