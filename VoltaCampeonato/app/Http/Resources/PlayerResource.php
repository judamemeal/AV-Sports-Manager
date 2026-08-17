<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'team_id' => $this->team_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'jersey_number' => $this->jersey_number,
            'position' => $this->position?->value,
            'position_label' => $this->position?->label(),
            'course' => $this->course,
            'parallel' => $this->parallel,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'photo_path' => $this->photo_path,
            'photo_url' => $this->photo_path ? asset('storage/' . $this->photo_path) : null,
            'is_active' => $this->is_active,
            'team' => new TeamResource($this->whenLoaded('team')),
            'goals_count' => $this->whenCounted('goals'),
            'yellow_cards_count' => $this->whenCounted('yellowCards'),
            'red_cards_count' => $this->whenCounted('redCards'),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
