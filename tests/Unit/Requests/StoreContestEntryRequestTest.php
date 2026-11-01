<?php

use App\Http\Requests\StoreContestEntryRequest;

describe('StoreContestEntryRequest', function () {
    describe('validation rules', function () {
        it('passes validation with all fields', function () {
            $request = StoreContestEntryRequest::create('POST', '/contests/1/entries', [
                'title' => 'My Entry',
                'description' => 'Entry description',
                'author_name' => 'John Doe',
                'author_department' => 'Engineering',
                'fields_data' => ['key' => 'value'],
            ]);

            expect($request->validate($request->rules()))->toBe([
                'title' => 'My Entry',
                'description' => 'Entry description',
                'author_name' => 'John Doe',
                'author_department' => 'Engineering',
                'fields_data' => ['key' => 'value'],
            ]);
        });

        it('passes validation with only required title', function () {
            $request = StoreContestEntryRequest::create('POST', '/contests/1/entries', [
                'title' => 'Minimal Entry',
            ]);

            expect($request->validate($request->rules()))->toBe([
                'title' => 'Minimal Entry',
            ]);
        });

        it('passes validation with nullable fields omitted', function () {
            $request = StoreContestEntryRequest::create('POST', '/contests/1/entries', [
                'title' => 'Entry Without Optional Fields',
            ]);

            expect($request->validate($request->rules()))->toBe([
                'title' => 'Entry Without Optional Fields',
            ]);
        });

        it('passes validation with empty fields_data array', function () {
            $request = StoreContestEntryRequest::create('POST', '/contests/1/entries', [
                'title' => 'Entry',
                'fields_data' => [],
            ]);

            expect($request->validate($request->rules()))->toBe([
                'title' => 'Entry',
                'fields_data' => [],
            ]);
        });

        it('fails validation when title is missing', function () {
            $request = StoreContestEntryRequest::create('POST', '/contests/1/entries', []);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when title is empty string', function () {
            $request = StoreContestEntryRequest::create('POST', '/contests/1/entries', [
                'title' => '',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when title is not a string', function () {
            $request = StoreContestEntryRequest::create('POST', '/contests/1/entries', [
                'title' => 123,
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when title exceeds 255 characters', function () {
            $request = StoreContestEntryRequest::create('POST', '/contests/1/entries', [
                'title' => str_repeat('a', 256),
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('passes validation with title at max length', function () {
            $request = StoreContestEntryRequest::create('POST', '/contests/1/entries', [
                'title' => str_repeat('a', 255),
            ]);

            expect($request->validate($request->rules()))->toBe([
                'title' => str_repeat('a', 255),
            ]);
        });

        it('fails validation when description exceeds 2000 characters', function () {
            $request = StoreContestEntryRequest::create('POST', '/contests/1/entries', [
                'title' => 'Entry',
                'description' => str_repeat('a', 2001),
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('passes validation with description at max length', function () {
            $request = StoreContestEntryRequest::create('POST', '/contests/1/entries', [
                'title' => 'Entry',
                'description' => str_repeat('a', 2000),
            ]);

            expect($request->validate($request->rules()))->toBe([
                'title' => 'Entry',
                'description' => str_repeat('a', 2000),
            ]);
        });

        it('fails validation when author_name exceeds 255 characters', function () {
            $request = StoreContestEntryRequest::create('POST', '/contests/1/entries', [
                'title' => 'Entry',
                'author_name' => str_repeat('a', 256),
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when author_department exceeds 255 characters', function () {
            $request = StoreContestEntryRequest::create('POST', '/contests/1/entries', [
                'title' => 'Entry',
                'author_department' => str_repeat('a', 256),
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when fields_data is not an array', function () {
            $request = StoreContestEntryRequest::create('POST', '/contests/1/entries', [
                'title' => 'Entry',
                'fields_data' => 'not-an-array',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });
    });

    describe('authorization', function () {
        it('allows any authenticated user to authorize', function () {
            $request = StoreContestEntryRequest::create('POST', '/contests/1/entries', [
                'title' => 'Entry',
            ]);

            expect($request->authorize())->toBeTrue();
        });
    });
});
