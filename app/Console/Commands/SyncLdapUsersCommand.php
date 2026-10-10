<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use LdapRecord\LdapRecordException;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use LdapRecord\Models\Collection;

final class SyncLdapUsersCommand extends Command
{
    protected $signature = 'ldap:sync
                            {--dry-run : Preview changes without applying them}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Sync local users with LDAP directory credentials';

    /**
     * Safely extract a string attribute from LDAP user.
     */
    private function attr(LdapUser $ldapUser, string $key): string
    {
        $value = $ldapUser->getFirstAttribute($key);

        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            return $value;
        }

        throw new \InvalidArgumentException(sprintf('Expected string for attribute %s, got %s', $key, get_debug_type($value)));
    }

    /**
     * Safely extract a nullable string attribute from LDAP user.
     */
    private function attrNullable(LdapUser $ldapUser, string $key): ?string
    {
        $value = $ldapUser->getFirstAttribute($key);

        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        throw new \InvalidArgumentException(sprintf('Expected string for attribute %s, got %s', $key, get_debug_type($value)));
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        if (! $force && ! $this->confirm('This will sync all users with LDAP. Continue?')) {
            return Command::FAILURE;
        }

        /** @var string $baseDn */
        $baseDn = config('ldap.connections.default.base_dn');

        if ($baseDn === '') {
            $this->error('LDAP base_dn is not configured.');

            return Command::FAILURE;
        }

        $this->info('Searching LDAP directory...');

        /** @var LdapUser $ldapUserModel */
        $ldapUserModel = new LdapUser;
        $query = $ldapUserModel->newQuery();

        try {
            /** @var Collection<int, LdapUser> $ldapUsers */
            $ldapUsers = $query->in($baseDn)
                ->where('objectclass', 'person')
                ->where('!(useraccountcontrol:1.2.840.113556.1.4.803:=2)')
                ->get();
        } catch (LdapRecordException $e) {
            $this->error(sprintf('LDAP connection failed: %s', $e->getMessage()));

            return Command::FAILURE;
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        $this->newLine();
        $this->output->writeln(sprintf(
            '%-10s %-20s %-25s %-20s %-20s',
            'Action', 'Login', 'Name', 'Department', 'Position'
        ));
        $this->output->writeln(str_repeat('-', 95));

        /** @var LdapUser $ldapUser */
        foreach ($ldapUsers as $ldapUser) {
            $samAccountName = $this->attr($ldapUser, 'samaccountname');
            $cn = $this->attr($ldapUser, 'cn');
            $mail = $this->attr($ldapUser, 'mail');
            $department = $this->attrNullable($ldapUser, 'department');
            $title = $this->attrNullable($ldapUser, 'title');
            $city = $this->attrNullable($ldapUser, 'l');
            $phone = $this->attrNullable($ldapUser, 'telephonenumber');
            $guid = $this->attr($ldapUser, 'objectguid');

            if ($samAccountName === '') {
                $this->warn(sprintf('Skipping user without samaccountname: %s', $cn ?: 'unknown'));
                $skipped++;

                continue;
            }

            // Find existing user by login or guid
            /** @var User|null $user */
            $user = User::where('login', $samAccountName)
                ->orWhere('guid', $guid)
                ->first();

            if ($user === null) {
                // Create new user
                if ($dryRun) {
                    $this->output->writeln(sprintf(
                        '%-10s %-20s %-25s %-20s %-20s',
                        'CREATE', $samAccountName, $cn ?: '', $department ?? '', $title ?? ''
                    ));
                    $created++;

                    continue;
                }

                try {
                    $user = User::create([
                        'login' => $samAccountName,
                        'name' => $cn !== '' ? $cn : $samAccountName,
                        'email' => $mail,
                        'guid' => $guid,
                        'domain' => config('app.name'),
                        'role' => User::ROLE_USER,
                    ]);

                    $this->output->writeln(sprintf(
                        '%-10s %-20s %-25s %-20s %-20s',
                        'CREATED', $samAccountName, $user->name, $department ?? '', $title ?? ''
                    ));
                    $created++;
                } catch (\Throwable $e) {
                    $this->error(sprintf(
                        'Failed to create user %s: %s',
                        $samAccountName,
                        $e->getMessage()
                    ));
                    $errors++;
                }

                continue;
            }

            // Update existing user
            $newName = $cn !== '' ? $cn : $samAccountName;
            if ($user->name !== $newName) {
                $user->name = $newName;
            }

            if ($user->email !== $mail) {
                $user->email = $mail;
            }

            if ($user->department !== $department) {
                $user->department = $department;
            }

            if ($user->position !== $title) {
                $user->position = $title;
            }

            if ($user->city_code !== $city) {
                $user->city_code = $city;
            }

            if ($user->phone !== $phone) {
                $user->phone = $phone;
            }

            $hasChanges = (
                $user->isDirty('name')
                || $user->isDirty('email')
                || $user->isDirty('department')
                || $user->isDirty('position')
                || $user->isDirty('city_code')
                || $user->isDirty('phone')
            );

            if ($hasChanges) {
                if ($dryRun) {
                    $this->output->writeln(sprintf(
                        '%-10s %-20s %-25s %-20s %-20s',
                        'UPDATE', $user->login, $user->name, $department ?? '', $title ?? ''
                    ));
                    $updated++;

                    continue;
                }

                try {
                    $user->save();

                    $this->output->writeln(sprintf(
                        '%-10s %-20s %-25s %-20s %-20s',
                        'UPDATED', $user->login, $user->name, $department ?? '', $title ?? ''
                    ));
                    $updated++;
                } catch (\Throwable $e) {
                    $this->error(sprintf(
                        'Failed to update user %s: %s',
                        $user->login,
                        $e->getMessage()
                    ));
                    $errors++;
                }
            } else {
                $skipped++;
            }
        }

        $this->newLine();
        $this->info('Sync complete!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Created', $created],
                ['Updated', $updated],
                ['Skipped (no changes)', $skipped],
                ['Errors', $errors],
            ]
        );

        return Command::SUCCESS;
    }
}
