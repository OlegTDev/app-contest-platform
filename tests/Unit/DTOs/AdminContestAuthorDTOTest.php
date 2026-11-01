<?php

use App\DTOs\AdminContestAuthorDTO;

describe('AdminContestAuthorDTO', function () {
    it('can be created from array with all fields', function () {
        $dto = AdminContestAuthorDTO::fromArray([
            'id' => 42,
            'name' => 'John Doe',
        ]);

        expect($dto->id)->toBe(42)
            ->and($dto->name)->toBe('John Doe');
    });

    it('uses default values when fields are missing', function () {
        $dto = AdminContestAuthorDTO::fromArray([]);

        expect($dto->id)->toBe(0)
            ->and($dto->name)->toBe('Unknown');
    });

    it('casts id to integer', function () {
        $dto = AdminContestAuthorDTO::fromArray([
            'id' => '123',
            'name' => 'Test',
        ]);

        expect($dto->id)->toBe(123)
            ->and($dto->id)->toBeInt();
    });

    it('casts name to string', function () {
        $dto = AdminContestAuthorDTO::fromArray([
            'id' => 1,
            'name' => 456,
        ]);

        expect($dto->name)->toBe('456')
            ->and($dto->name)->toBeString();
    });

    it('handles null-like values in array', function () {
        $dto = AdminContestAuthorDTO::fromArray([
            'id' => null,
            'name' => null,
        ]);

        expect($dto->id)->toBe(0)
            ->and($dto->name)->toBe('Unknown');
    });
});
