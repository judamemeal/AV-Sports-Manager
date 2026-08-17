<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'championship_id' => $this->championship_id,
            'phase_id' => $this->phase_id,
            'group_id' => $this->group_id,
            'round_id' => $this->round_id,
            'home_team_id' => $this->home_team_id,
            'away_team_id' => $this->away_team_id,
            'match_date' => $this->match_date?->format('Y-m-d'),
            'match_time' => $this->match_time,
            'venue' => $this->venue,
            'referee' => $this->referee,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'status_color' => $this->status?->color(),
            'home_score' => $this->home_score,
            'away_score' => $this->away_score,
            'bracket_position' => $this->bracket_position,
            'home_team' => new TeamResource($this->whenLoaded('homeTeam')),
            'away_team' => new TeamResource($this->whenLoaded('awayTeam')),
            'phase' => new PhaseResource($this->whenLoaded('phase')),
            'group' => new GroupResource($this->whenLoaded('group')),
            'round' => $this->whenLoaded('round', fn () => [
                'id' => $this->round->id,
                'name' => $this->round->name,
                'round_number' => $this->round->round_number,
            ]),
            'events' => MatchEventResource::collection($this->whenLoaded('events')),
            'championship' => new ChampionshipResource($this->whenLoaded('championship')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
