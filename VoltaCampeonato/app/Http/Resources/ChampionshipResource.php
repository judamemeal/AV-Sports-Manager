<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChampionshipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'year' => $this->year,
            'sport' => $this->sport,
            'category' => $this->category,
            'course_level' => $this->course_level,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'description' => $this->description,
            'regulations' => $this->regulations,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'status_color' => $this->status?->color(),
            'teams_count' => $this->whenCounted('teams'),
            'matches_count' => $this->whenCounted('matches'),
            'teams' => TeamResource::collection($this->whenLoaded('teams')),
            'phases' => PhaseResource::collection($this->whenLoaded('phases')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
