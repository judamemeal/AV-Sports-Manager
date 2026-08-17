<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StandingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'championship_id' => $this->championship_id,
            'group_id' => $this->group_id,
            'phase_id' => $this->phase_id,
            'team_id' => $this->team_id,
            'played' => $this->played,
            'won' => $this->won,
            'drawn' => $this->drawn,
            'lost' => $this->lost,
            'goals_for' => $this->goals_for,
            'goals_against' => $this->goals_against,
            'goal_difference' => $this->goal_difference,
            'points' => $this->points,
            'position' => $this->position,
            'team' => new TeamResource($this->whenLoaded('team')),
            'group' => new GroupResource($this->whenLoaded('group')),
        ];
    }
}
