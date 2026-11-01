<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class AssignRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var User $authUser */
        $authUser = $this->user();

        return $authUser->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array|string[]>
     */
    public function rules(): array
    {
        return [
            'role' => [
                'required',
                'string',
                'in:'.implode(',', array_map(fn (UserRole $r) => $r->value, UserRole::cases())),
            ],
        ];
    }
}
