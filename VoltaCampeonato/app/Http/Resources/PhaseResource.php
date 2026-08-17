<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'championship_id' => $this->championship_id,
            'tournament_format_id' => $this->tournament_format_id,
            'name' => $this->name,
            'type' => $this->type?->value,
            'type_label' => $this->type?->label(),
            'order' => $this->order,
            'team_count' => $this->team_count,
            'configuration' => $this->configuration,
            'is_completed' => $this->is_completed,
            'groups' => GroupResource::collection($this->whenLoaded('groups')),
            'rounds' => $this->whenLoaded('rounds'),
            'matches' => MatchResource::collection($this->whenLoaded('matches')),
        ];
    }
}
