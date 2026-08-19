<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChampionshipController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\TournamentFormatController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — VoltaCampeonato
|--------------------------------------------------------------------------
*/

// ──────────────────────────────────────────
// Public routes (no authentication required)
// ──────────────────────────────────────────

Route::post('/login', [AuthController::class, 'login']);

// Public read-only endpoints
Route::get('/campeonatos', [ChampionshipController::class, 'index']);
Route::get('/campeonatos/{championship}', [ChampionshipController::class, 'show']);
Route::get('/campeonatos/{championship}/posiciones', [ChampionshipController::class, 'standings']);
Route::get('/campeonatos/{championship}/goleadores', [ChampionshipController::class, 'scorers']);
Route::get('/campeonatos/{championship}/estadisticas', [ChampionshipController::class, 'statistics']);
Route::get('/campeonatos/{championship}/calendario', [ChampionshipController::class, 'calendar']);
Route::get('/campeonatos/{championship}/fases', [ChampionshipController::class, 'phases']);
Route::get('/campeonatos/{championship}/cruces', [ChampionshipController::class, 'brackets']);
Route::get('/campeonatos/{championship}/formato', [TournamentFormatController::class, 'show']);

Route::get('/equipos', [TeamController::class, 'index']);
Route::get('/equipos/{team}', [TeamController::class, 'show']);
Route::get('/equipos/{team}/jugadores', [TeamController::class, 'players']);
Route::get('/equipos/{team}/partidos', [TeamController::class, 'matches']);
Route::get('/equipos/{team}/estadisticas', [TeamController::class, 'statistics']);

Route::get('/jugadores', [PlayerController::class, 'index']);
Route::get('/jugadores/{player}', [PlayerController::class, 'show']);
Route::get('/jugadores/{player}/estadisticas', [PlayerController::class, 'statistics']);

Route::get('/partidos', [MatchController::class, 'index']);
Route::get('/partidos/{match}', [MatchController::class, 'show']);
Route::get('/partidos/{match}/eventos', [MatchController::class, 'events']);
Route::get('/partidos/en-vivo', [MatchController::class, 'live']);


// ──────────────────────────────────────────
// Authenticated routes
// ──────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ──────────────────────────────────────
    // Admin-only routes
    // ──────────────────────────────────────
    Route::middleware('admin')->group(function () {

        // Dashboard
        Route::get('/admin/dashboard', [DashboardController::class, 'index']);

        // Championships CRUD
        Route::post('/campeonatos', [ChampionshipController::class, 'store']);
        Route::put('/campeonatos/{championship}', [ChampionshipController::class, 'update']);
        Route::delete('/campeonatos/{championship}', [ChampionshipController::class, 'destroy']);

        // Teams CRUD
        Route::post('/equipos', [TeamController::class, 'store']);
        Route::put('/equipos/{team}', [TeamController::class, 'update']);
        Route::delete('/equipos/{team}', [TeamController::class, 'destroy']);

        // Players CRUD
        Route::post('/jugadores', [PlayerController::class, 'store']);
        Route::put('/jugadores/{player}', [PlayerController::class, 'update']);
        Route::delete('/jugadores/{player}', [PlayerController::class, 'destroy']);

        // Matches CRUD
        Route::post('/partidos', [MatchController::class, 'store']);
        Route::put('/partidos/{match}', [MatchController::class, 'update']);
        Route::delete('/partidos/{match}', [MatchController::class, 'destroy']);

        // Match control (jugar partido)
        Route::post('/partidos/{match}/iniciar', [MatchController::class, 'start']);
        Route::post('/partidos/{match}/eventos', [MatchController::class, 'recordEvent']);
        Route::delete('/partidos/{match}/eventos/{event}', [MatchController::class, 'deleteEvent']);
        Route::post('/partidos/{match}/finalizar', [MatchController::class, 'finish']);

        // Tournament format generation
        Route::post('/campeonatos/{championship}/generar-formato', [TournamentFormatController::class, 'generate']);
        Route::post('/campeonatos/{championship}/validar-formato', [TournamentFormatController::class, 'validate']);
        Route::put('/formatos/{format}', [TournamentFormatController::class, 'update']);
        Route::put('/grupos/{group}/equipos', [TournamentFormatController::class, 'updateGroupTeams']);

        // Users management
        Route::get('/usuarios', [UserController::class, 'index']);
        Route::post('/usuarios', [UserController::class, 'store']);
        Route::put('/usuarios/{user}', [UserController::class, 'update']);
        Route::delete('/usuarios/{user}', [UserController::class, 'destroy']);
    });
});
