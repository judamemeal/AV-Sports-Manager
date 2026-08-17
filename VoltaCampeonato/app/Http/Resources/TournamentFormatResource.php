<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TournamentFormatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'championship_id' => $this->championship_id,
            'type' => $this->type?->value,
            'type_label' => $this->type?->label(),
            'configuration' => $this->configuration,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'is_round_trip' => $this->is_round_trip,
            'phases' => PhaseResource::collection($this->whenLoaded('phases')),
            'championship' => new ChampionshipResource($this->whenLoaded('championship')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
