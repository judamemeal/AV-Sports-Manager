<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'championship_id' => ['sometimes', 'exists:championships,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'course' => ['nullable', 'string', 'max:100'],
            'parallel' => ['nullable', 'string', 'max:10'],
            'category' => ['nullable', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'color' => ['nullable', 'string', 'max:20'],
            'captain_name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
