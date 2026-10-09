<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

final class UserAssignRoleCommand extends Command
{
    protected $signature = 'user:assign-role
                            {email : The email address of the user}
                            {role : The role to assign (admin, moderator, user)}';

    protected $description = 'Assign a role to a user by email';

    /**
     * Valid role values.
     *
     * @var list<string>
     */
    private const VALID_ROLES = [User::ROLE_ADMIN, User::ROLE_MODERATOR, User::ROLE_USER];

    public function handle(): int
    {
        $email = $this->argument('email');
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

        // Validate email format
        $validator = Validator::make(
            ['email' => $email],
            ['email' => 'required|email']
        );

        if ($validator->fails()) {
            $this->error(sprintf('Invalid email format: %s', $email));

            return Command::FAILURE;
        }

        // Find user
        $user = User::where('email', $email)->first();

        if ($user === null) {
            $this->error(sprintf('User with email "%s" not found.', $email));

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
