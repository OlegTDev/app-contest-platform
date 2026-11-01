<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContestEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'author_department' => ['nullable', 'string', 'max:255'],
            'fields_data' => ['nullable', 'array'],
        ];
    }
}
