<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'phase_id' => $this->phase_id,
            'name' => $this->name,
            'qualified_count' => $this->qualified_count,
            'teams' => TeamResource::collection($this->whenLoaded('teams')),
            'standings' => StandingResource::collection($this->whenLoaded('standings')),
            'matches' => MatchResource::collection($this->whenLoaded('matches')),
        ];
    }
}
