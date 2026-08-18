<?php

namespace App\Http\Requests\Match;

use Illuminate\Foundation\Http\FormRequest;

class StoreMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'championship_id' => ['required', 'exists:championships,id'],
            'phase_id' => ['nullable', 'exists:phases,id'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'round_id' => ['nullable', 'exists:rounds,id'],
            'home_team_id' => ['nullable', 'exists:teams,id'],
            'away_team_id' => ['nullable', 'exists:teams,id', 'different:home_team_id'],
            'match_duration' => ['nullable', 'integer', 'min:1', 'max:200'],
            'match_date' => ['nullable', 'date'],
            'match_time' => ['nullable', 'date_format:H:i'],
            'venue' => ['nullable', 'string', 'max:255'],
            'referee' => ['nullable', 'string', 'max:255'],
        ];
    }
}
