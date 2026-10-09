<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignRoleRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class UserController extends Controller
{
    public function __construct(
        private UserService $userService,
    ) {}

    /**
     * Display a paginated list of users.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) $request->input('per_page', 15);
        $users = $this->userService->getPaginatedUsers($perPage);

        return Inertia::render('admin/users/Index', [
            'users' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'next_page_url' => $users->nextPageUrl(),
                'prev_page_url' => $users->previousPageUrl(),
            ],
            'available_roles' => [
                ['value' => User::ROLE_ADMIN, 'label' => 'Admin'],
                ['value' => User::ROLE_MODERATOR, 'label' => 'Moderator'],
                ['value' => User::ROLE_USER, 'label' => 'User'],
            ],
        ]);
    }

    /**
     * Update a user's role.
     */
    public function updateRole(AssignRoleRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $this->userService->assignRole($user, $validated['role']);

        return redirect()->back()->with('success', 'Role updated successfully.');
    }
}
