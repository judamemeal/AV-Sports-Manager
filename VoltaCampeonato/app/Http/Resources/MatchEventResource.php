<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatchEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'game_match_id' => $this->game_match_id,
            'player_id' => $this->player_id,
            'team_id' => $this->team_id,
            'type' => $this->type?->value,
            'type_label' => $this->type?->label(),
            'type_icon' => $this->type?->icon(),
            'minute' => $this->minute,
            'description' => $this->description,
            'extra_data' => $this->extra_data,
            'player' => new PlayerResource($this->whenLoaded('player')),
            'team' => new TeamResource($this->whenLoaded('team')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
