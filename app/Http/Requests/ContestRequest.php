<?php

namespace App\Http\Requests;

use App\Enums\ContestType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<property-of<\App\Models\Contest>, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'title' => ['required', 'string'],

            'type' => ['required', Rule::enum(ContestType::class)],
            'project_schema' => ['array', 'nullable'],
            'project_schema.*.name' => ['required', 'string'],
            'project_schema.*.label' => ['required', 'string'],
            'project_schema.*.type' => ['required', 'string'],
            'project_schema.*.required' => ['required', 'boolean'],
            'project_schema.*.select_values' => ['string'],
        ];
    }
}
