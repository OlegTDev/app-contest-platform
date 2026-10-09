<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

final class UserAssignRoleCommand extends Command
{
    protected $signature = 'user:assign-role
                            {login : The login of the user}
                            {role : The role to assign (admin, moderator, user)}';

    protected $description = 'Assign a role to a user by login';

    /**
     * Valid role values.
     *
     * @var list<string>
     */
    private const VALID_ROLES = [User::ROLE_ADMIN, User::ROLE_MODERATOR, User::ROLE_USER];

    public function handle(): int
    {
        $login = $this->argument('login');
        $role = $this->argument('role');

        // Validate role
        if (! in_array($role, self::VALID_ROLES, strict: true)) {
            $this->error(sprintf(
                'Invalid role "%s". Allowed values: %s',
                $role,
                implode(', ', self::VALID_ROLES)
            ));

            return Command::FAILURE;
        }

        // Find user
        $user = User::where('login', $login)->first();

        if ($user === null) {
            $this->error(sprintf('User with login "%s" not found.', $login));

            return Command::FAILURE;
        }

        $user->role = $role;
        $user->save();

        $this->info(sprintf(
            'Role "%s" assigned to user "%s" (ID: %d).',
            $role,
            $user->name,
            $user->id
        ));

        return Command::SUCCESS;
    }
}
