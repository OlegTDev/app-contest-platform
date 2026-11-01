<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuizEntryRequest extends FormRequest
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
            'question' => ['required', 'string', 'max:2000'],
            'options' => ['required', 'array', 'min:2'],
            'options.*.text' => ['required', 'string', 'max:500'],
            'options.*.is_correct' => ['required', 'boolean'],
            'type' => ['required', 'in:single,multiple'],
            'sort_order' => ['nullable', 'integer'],
            'show_from' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'show_until' => ['nullable', 'date_format:Y-m-d H:i:s'],
        ];
    }
}
