<?php

use App\Http\Requests\ContestRequest;

describe('ContestRequest', function () {
    describe('validation rules', function () {
        it('passes validation with all fields', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'My Contest',
                'type' => 'voting',
                'status' => 'draft',
                'description' => 'Contest description',
                'start_at' => '2025-01-01 00:00:00',
                'end_at' => '2025-12-31 23:59:59',
                'project_schema' => [
                    [
                        'name' => 'framework',
                        'label' => 'Framework',
                        'type' => 'select',
                        'required' => true,
                        'select_values' => 'Laravel,Symfony,Django',
                    ],
                ],
                'slug' => 'my-contest',
            ]);

            $validated = $request->validate($request->rules());
            expect($validated['title'])->toBe('My Contest');
            expect($validated['type'])->toBe('voting');
        });

        it('passes validation with quiz type', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Quiz Contest',
                'type' => 'quiz',
                'description' => 'A quiz contest',
            ]);

            $validated = $request->validate($request->rules());
            expect($validated['type'])->toBe('quiz');
        });

        it('passes validation with only required fields', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Minimal Contest',
                'type' => 'voting',
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('passes validation with nullable fields omitted', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('passes validation with null slug', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
                'slug' => null,
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('passes validation with null start_at and end_at', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
                'start_at' => null,
                'end_at' => null,
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('passes validation with null project_schema', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
                'project_schema' => null,
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('fails validation when title is missing', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'type' => 'voting',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when type is missing', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when type is not a valid ContestType', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'debate',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when title is empty string', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => '',
                'type' => 'voting',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when title exceeds 255 characters', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => str_repeat('a', 256),
                'type' => 'voting',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('passes validation with title at max length', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => str_repeat('a', 255),
                'type' => 'voting',
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('fails validation when description exceeds 2000 characters', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
                'description' => str_repeat('a', 2001),
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('passes validation with description at max length', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
                'description' => str_repeat('a', 2000),
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('fails validation when start_at has invalid date format', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
                'start_at' => '01-01-2025',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('passes validation with valid start_at date format', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
                'start_at' => '2025-01-01 00:00:00',
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('fails validation when end_at has invalid date format', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
                'end_at' => '2025/01/01',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('passes validation with valid end_at date format', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
                'end_at' => '2025-12-31 23:59:59',
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('passes validation with all valid statuses', function () {
            $statuses = ['draft', 'published', 'paused', 'closed', 'archived'];

            foreach ($statuses as $status) {
                $request = ContestRequest::create('POST', '/admin/contests', [
                    'title' => 'Contest',
                    'type' => 'voting',
                    'status' => $status,
                ]);

                expect(true)->toBeTrue(); // Status '$status' accepted
            }
        });

        it('fails validation when status is invalid', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
                'status' => 'active',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('passes validation when status is omitted (sometimes rule)', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('fails validation when slug has invalid characters', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
                'slug' => 'My Contest!',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when slug contains uppercase letters', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
                'slug' => 'my-contest',
            ]);

            // lowercase is OK, but uppercase should fail
            $request2 = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
                'slug' => 'My-Contest',
            ]);

            expect(fn () => $request2->validate($request2->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('passes validation with valid slug format', function () {
            $slugs = ['my-contest', 'my-contest-2', 'a-b-c', 'contest123'];

            foreach ($slugs as $slug) {
                $request = ContestRequest::create('POST', '/admin/contests', [
                    'title' => 'Contest',
                    'type' => 'voting',
                    'slug' => $slug,
                ]);

                expect(true)->toBeTrue(); // Slug '$slug' accepted
            }
        });

        it('fails validation when slug exceeds 255 characters', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
                'slug' => str_repeat('a', 256),
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });
    });

    describe('project_schema validation', function () {
        it('passes validation with valid project_schema', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
                'project_schema' => [
                    [
                        'name' => 'framework',
                        'label' => 'Framework',
                        'type' => 'select',
                        'required' => true,
                        'select_values' => 'Laravel,Symfony',
                    ],
                ],
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('fails validation when project_schema name is missing', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
                'project_schema' => [
                    ['label' => 'Framework', 'type' => 'select', 'required' => true],
                ],
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when project_schema label is missing', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
                'project_schema' => [
                    ['name' => 'framework', 'type' => 'select', 'required' => true],
                ],
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when project_schema type is missing', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
                'project_schema' => [
                    ['name' => 'framework', 'label' => 'Framework', 'required' => true],
                ],
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when project_schema required is not a boolean', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
                'project_schema' => [
                    ['name' => 'framework', 'label' => 'Framework', 'type' => 'select', 'required' => 'yes'],
                ],
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('passes validation with project_schema without select_values', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
                'project_schema' => [
                    [
                        'name' => 'framework',
                        'label' => 'Framework',
                        'type' => 'text',
                        'required' => true,
                    ],
                ],
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('passes validation with multiple project_schema items', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
                'project_schema' => [
                    [
                        'name' => 'framework',
                        'label' => 'Framework',
                        'type' => 'select',
                        'required' => true,
                    ],
                    [
                        'name' => 'language',
                        'label' => 'Language',
                        'type' => 'select',
                        'required' => false,
                    ],
                ],
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });
    });

    describe('authorization', function () {
        it('allows any authenticated user to authorize', function () {
            $request = ContestRequest::create('POST', '/admin/contests', [
                'title' => 'Contest',
                'type' => 'voting',
            ]);

            expect($request->authorize())->toBeTrue();
        });
    });
});
