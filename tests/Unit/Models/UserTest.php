<?php

use App\Models\Contest;
use App\Models\ContestEntry;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Fortify\Contracts\PasskeyUser;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;

describe('User model', function () {
    it('can create a user with all attributes', function () {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        expect($user->name)->toBe('John Doe')
            ->and($user->email)->toBe('john@example.com')
            ->and($user->password)->not->toBe('password');
    });

    it('has correct fillable attributes', function () {
        $user = new User;

        expect($user->getFillable())
            ->toBe(['name', 'email', 'password', 'guid', 'domain']);
    });

    it('has correct hidden attributes', function () {
        $user = new User;

        expect($user->getHidden())
            ->toBe(['password']);
    });

    it('casts password as hashed', function () {
        $user = User::factory()->create(['password' => 'plaintext']);

        expect($user->password)->not->toBe('plaintext')
            ->and(password_verify('plaintext', $user->password))->toBeTrue();
    });

    describe('relationships', function () {
        it('has many contests', function () {
            $user = User::factory()->create();
            Contest::factory()->create(['user_id' => $user->id]);
            Contest::factory()->create(['user_id' => $user->id]);

            expect($user->fresh('contests')->contests)->toHaveCount(2);
        });

        it('has many votes', function () {
            $user = User::factory()->create();
            $contest = Contest::factory()->create();
            $firstEntry = ContestEntry::factory()->create(['contest_id' => $contest->id]);
            $secondEntry = ContestEntry::factory()->create(['contest_id' => $contest->id]);

            $user->votes()->createMany([
                ['contest_id' => $contest->id, 'entry_id' => $firstEntry->id],
                ['contest_id' => $contest->id, 'entry_id' => $secondEntry->id],
            ]);

            expect($user->fresh('votes')->votes)->toHaveCount(2);
        });
    });

    describe('implements', function () {
        it('implements MustVerifyEmail', function () {
            $user = User::factory()->create();
            expect($user)->toBeInstanceOf(MustVerifyEmail::class);
        });

        it('implements PasskeyUser', function () {
            $user = User::factory()->create();
            expect($user)->toBeInstanceOf(PasskeyUser::class);
        });

        it('implements LdapAuthenticatable', function () {
            $user = User::factory()->create();
            expect($user)->toBeInstanceOf(LdapAuthenticatable::class);
        });
    });

    describe('edge cases', function () {
        it('allows an empty name', function () {
            $user = User::factory()->create(['name' => '']);
            expect($user->name)->toBe('');
        });

        it('handles null guid', function () {
            $user = User::factory()->create(['guid' => null]);
            expect($user->guid)->toBeNull();
        });

        it('handles null domain', function () {
            $user = User::factory()->create(['domain' => null]);
            expect($user->domain)->toBeNull();
        });
    });
});
