<?php

namespace App\Http\Requests\TournamentFormat;

use Illuminate\Foundation\Http\FormRequest;

class GenerateFormatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:league,groups,knockout,groups_knockout,custom'],
            'is_round_trip' => ['sometimes', 'boolean'],
            'team_ids' => ['required', 'array', 'min:2'],
            'team_ids.*' => ['exists:teams,id'],
            'phases' => ['required_if:type,custom', 'array'],
            'phases.*.name' => ['required_with:phases', 'string'],
            'phases.*.type' => ['required_with:phases', 'string', 'in:group,knockout,league,play_in,final'],
            'phases.*.team_count' => ['required_with:phases', 'integer', 'min:2'],
            'phases.*.configuration' => ['nullable', 'array'],
            // Group-specific
            'groups_count' => ['required_if:type,groups,groups_knockout', 'integer', 'min:1'],
            'teams_per_group' => ['required_if:type,groups,groups_knockout', 'integer', 'min:2'],
            'qualified_per_group' => ['required_if:type,groups,groups_knockout', 'integer', 'min:1'],
            // Schedule
            'start_date' => ['nullable', 'date'],
            'match_time' => ['nullable', 'date_format:H:i'],
            'venues' => ['nullable', 'array'],
            'venues.*' => ['string', 'max:255'],
        ];
    }
}
