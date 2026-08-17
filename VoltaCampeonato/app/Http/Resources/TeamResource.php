<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'championship_id' => $this->championship_id,
            'name' => $this->name,
            'course' => $this->course,
            'parallel' => $this->parallel,
            'category' => $this->category,
            'logo_path' => $this->logo_path,
            'logo_url' => $this->logo_path ? asset('storage/' . $this->logo_path) : null,
            'color' => $this->color,
            'captain_name' => $this->captain_name,
            'is_active' => $this->is_active,
            'players_count' => $this->whenCounted('players'),
            'championship' => new ChampionshipResource($this->whenLoaded('championship')),
            'players' => PlayerResource::collection($this->whenLoaded('players')),
            'standings' => StandingResource::collection($this->whenLoaded('standings')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
