<?php

use App\Http\Requests\UpdateContestEntryRequest;

describe('UpdateContestEntryRequest', function () {
    describe('validation rules', function () {
        it('passes validation with all fields', function () {
            $request = UpdateContestEntryRequest::create('PATCH', '/contests/1/entries/1', [
                'title' => 'Updated Entry',
                'description' => 'Updated description',
                'author_name' => 'Jane Doe',
                'author_department' => 'Design',
                'fields_data' => ['updated' => 'data'],
            ]);

            expect($request->validate($request->rules()))->toBe([
                'title' => 'Updated Entry',
                'description' => 'Updated description',
                'author_name' => 'Jane Doe',
                'author_department' => 'Design',
                'fields_data' => ['updated' => 'data'],
            ]);
        });

        it('passes validation with only title', function () {
            $request = UpdateContestEntryRequest::create('PATCH', '/contests/1/entries/1', [
                'title' => 'Minimal Update',
            ]);

            expect($request->validate($request->rules()))->toBe([
                'title' => 'Minimal Update',
            ]);
        });

        it('passes validation with null description', function () {
            $request = UpdateContestEntryRequest::create('PATCH', '/contests/1/entries/1', [
                'title' => 'Entry',
                'description' => null,
            ]);

            expect($request->validate($request->rules()))->toBe([
                'title' => 'Entry',
                'description' => null,
            ]);
        });

        it('fails validation when title is missing', function () {
            $request = UpdateContestEntryRequest::create('PATCH', '/contests/1/entries/1', []);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when title is empty string', function () {
            $request = UpdateContestEntryRequest::create('PATCH', '/contests/1/entries/1', [
                'title' => '',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when title exceeds 255 characters', function () {
            $request = UpdateContestEntryRequest::create('PATCH', '/contests/1/entries/1', [
                'title' => str_repeat('a', 256),
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('passes validation with title at max length', function () {
            $request = UpdateContestEntryRequest::create('PATCH', '/contests/1/entries/1', [
                'title' => str_repeat('a', 255),
            ]);

            expect($request->validate($request->rules()))->toBe([
                'title' => str_repeat('a', 255),
            ]);
        });

        it('fails validation when description exceeds 2000 characters', function () {
            $request = UpdateContestEntryRequest::create('PATCH', '/contests/1/entries/1', [
                'title' => 'Entry',
                'description' => str_repeat('a', 2001),
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('passes validation with description at max length', function () {
            $request = UpdateContestEntryRequest::create('PATCH', '/contests/1/entries/1', [
                'title' => 'Entry',
                'description' => str_repeat('a', 2000),
            ]);

            expect($request->validate($request->rules()))->toBe([
                'title' => 'Entry',
                'description' => str_repeat('a', 2000),
            ]);
        });

        it('fails validation when author_name exceeds 255 characters', function () {
            $request = UpdateContestEntryRequest::create('PATCH', '/contests/1/entries/1', [
                'title' => 'Entry',
                'author_name' => str_repeat('a', 256),
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when author_department exceeds 255 characters', function () {
            $request = UpdateContestEntryRequest::create('PATCH', '/contests/1/entries/1', [
                'title' => 'Entry',
                'author_department' => str_repeat('a', 256),
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when fields_data is not an array', function () {
            $request = UpdateContestEntryRequest::create('PATCH', '/contests/1/entries/1', [
                'title' => 'Entry',
                'fields_data' => 'not-an-array',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('passes validation with empty fields_data array', function () {
            $request = UpdateContestEntryRequest::create('PATCH', '/contests/1/entries/1', [
                'title' => 'Entry',
                'fields_data' => [],
            ]);

            expect($request->validate($request->rules()))->toBe([
                'title' => 'Entry',
                'fields_data' => [],
            ]);
        });
    });

    describe('authorization', function () {
        it('allows any authenticated user to authorize', function () {
            $request = UpdateContestEntryRequest::create('PATCH', '/contests/1/entries/1', [
                'title' => 'Entry',
            ]);

            expect($request->authorize())->toBeTrue();
        });
    });
});
