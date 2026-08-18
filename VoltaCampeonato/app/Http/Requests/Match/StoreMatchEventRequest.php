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
            'player_id' => [
                'nullable',
                'exists:players,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $player = \App\Models\Player::find($value);
                        if ($player && $player->team_id != $this->team_id) {
                            $fail('El jugador no pertenece al equipo seleccionado.');
                        }
                    }
                },
            ],
            'team_id' => [
                'required',
                'exists:teams,id',
                function ($attribute, $value, $fail) {
                    $match = $this->route('match'); // assuming the route parameter is named 'match'
                    if ($match && $match->home_team_id != $value && $match->away_team_id != $value) {
                        $fail('El equipo no participa en este partido.');
                    }
                },
            ],
            'type' => ['required', 'string', 'in:goal,yellow_card,red_card,substitution'],
            'minute' => ['required', 'integer', 'min:0', 'max:200'],
            'description' => ['nullable', 'string', 'max:255'],
            'extra_data' => ['nullable', 'array'],
            'extra_data.player_in_id' => ['nullable', 'exists:players,id'],
            'extra_data.player_out_id' => ['nullable', 'exists:players,id'],
        ];
    }
}
