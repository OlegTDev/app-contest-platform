<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuizRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question' => ['required', 'string', 'max:1000'],
            'questions.*.type' => ['required', 'string', 'in:single,multiple'],
            'questions.*.correct_answer' => ['required'],
            'questions.*.options' => ['required_if:questions.*.type,single,multiple', 'array'],
            'questions.*.options.*' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'questions.min' => 'Quiz must have at least one question.',
            'questions.*.question.required' => 'Each question must have text.',
            'questions.*.type.required' => 'Each question must have a type.',
            'questions.*.type.in' => 'Question type must be either "single" or "multiple".',
            'questions.*.correct_answer.required' => 'Each question must have a correct answer.',
            'questions.*.options.required' => 'Each question must have answer options.',
            'questions.*.options.*.required' => 'Each option must have text.',
        ];
    }
}
