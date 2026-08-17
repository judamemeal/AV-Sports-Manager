<?php

namespace App\Http\Requests\Match;

use Illuminate\Foundation\Http\FormRequest;

class StoreMatchEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'player_id' => ['nullable', 'exists:players,id'],
            'team_id' => ['required', 'exists:teams,id'],
            'type' => ['required', 'string', 'in:goal,yellow_card,red_card,substitution'],
            'minute' => ['required', 'integer', 'min:0', 'max:200'],
            'description' => ['nullable', 'string', 'max:255'],
            'extra_data' => ['nullable', 'array'],
            'extra_data.player_in_id' => ['nullable', 'exists:players,id'],
            'extra_data.player_out_id' => ['nullable', 'exists:players,id'],
        ];
    }
}
