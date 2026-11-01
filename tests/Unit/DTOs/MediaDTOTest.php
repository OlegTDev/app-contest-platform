<?php

use App\DTOs\MediaDTO;
use App\Models\Media;

describe('MediaDTO', function () {
    it('can be created from media model', function () {
        $media = Media::factory()->create([
            'file_name' => 'test.jpg',
            'file_path' => 'media/test.jpg',
            'file_type' => 'image/jpeg',
            'file_extension' => 'jpg',
            'file_size' => 1024,
            'is_main' => true,
        ]);

        $dto = MediaDTO::from($media);

        expect($dto->id)->toBe($media->id)
            ->and($dto->fileName)->toBe('test.jpg')
            ->and($dto->fileUrl)->toBe($media->file_url)
            ->and($dto->fileType)->toBe('image/jpeg')
            ->and($dto->fileExtension)->toBe('jpg')
            ->and($dto->fileSize)->toBe(1024)
            ->and($dto->isMain)->toBeTrue();
    });

    it('converts to array with snake_case keys', function () {
        $media = Media::factory()->create([
            'file_name' => 'photo.png',
            'file_path' => 'media/photo.png',
            'file_type' => 'image/png',
            'file_extension' => 'png',
            'file_size' => 2048,
            'is_main' => false,
        ]);

        $dto = MediaDTO::from($media);
        $array = $dto->toArray();

        expect($array)->toHaveKeys([
            'id', 'file_name', 'file_url', 'thumbnail_url',
            'file_type', 'file_extension', 'file_size', 'is_main', 'created_at',
        ])
        ->and($array['file_name'])->toBe('photo.png')
        ->and($array['file_url'])->toBe($media->file_url)
        ->and($array['file_size'])->toBe(2048)
        ->and($array['is_main'])->toBeFalse();
    });

    it('handles null file_type with default', function () {
        $media = Media::factory()->create([
            'file_name' => 'test.pdf',
            'file_path' => 'media/test.pdf',
            'file_type' => null,
            'file_extension' => 'pdf',
            'file_size' => null,
            'is_main' => false,
        ]);

        $dto = MediaDTO::from($media);

        expect($dto->fileType)->toBe('unknown');
    });

    it('handles null file_extension with empty string', function () {
        $media = Media::factory()->create([
            'file_name' => 'test',
            'file_path' => 'media/test',
            'file_type' => 'application/octet-stream',
            'file_extension' => null,
            'file_size' => null,
            'is_main' => false,
        ]);

        $dto = MediaDTO::from($media);

        expect($dto->fileExtension)->toBe('');
    });

    it('handles null file_size', function () {
        $media = Media::factory()->create([
            'file_name' => 'test.jpg',
            'file_path' => 'media/test.jpg',
            'file_type' => 'image/jpeg',
            'file_extension' => 'jpg',
            'file_size' => null,
            'is_main' => false,
        ]);

        $dto = MediaDTO::from($media);

        expect($dto->fileSize)->toBeNull();
    });

    it('can create a collection from media', function () {
        $media1 = Media::factory()->create(['file_name' => 'file1.jpg', 'file_path' => 'media/file1.jpg']);
        $media2 = Media::factory()->create(['file_name' => 'file2.png', 'file_path' => 'media/file2.png']);
        $mediaCollection = collect([$media1, $media2]);

        $dtoCollection = MediaDTO::collection($mediaCollection);

        expect($dtoCollection)->toHaveCount(2)
            ->and($dtoCollection[0]->fileName)->toBe('file1.jpg')
            ->and($dtoCollection[1]->fileName)->toBe('file2.png');
    });

    it('formats created_at correctly', function () {
        $media = Media::factory()->create([
            'file_name' => 'test.jpg',
            'file_path' => 'media/test.jpg',
            'file_type' => 'image/jpeg',
            'file_extension' => 'jpg',
            'is_main' => false,
        ]);

        $dto = MediaDTO::from($media);

        expect($dto->createdAt)->toBeString()
            ->and(strlen($dto->createdAt))->toBe(16); // 'Y-m-d H:i' format
    });
});
