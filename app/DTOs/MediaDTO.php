<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class MediaDTO
{
    public function __construct(
        public int $id,
        public string $fileName,
        public string $fileUrl,
        public string $fileType,
        public string $fileExtension,
        public int|null $fileSize,
        public bool $isMain,
        public string $createdAt,
    ) {}

    /**
     * Map a collection of media to DTOs.
     *
     * @param \Illuminate\Database\Eloquent\Collection<int, \App\Models\Media> $media
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function collection(\Illuminate\Support\Collection $media): \Illuminate\Support\Collection
    {
        return $media->map(fn (\App\Models\Media $m): self => self::from($m));
    }

    public static function from(\App\Models\Media $media): self
    {
        return new self(
            id: $media->id,
            fileName: $media->file_name,
            fileUrl: $media->file_url,
            fileType: $media->file_type ?? 'unknown',
            fileExtension: $media->file_extension ?? '',
            fileSize: $media->file_size,
            isMain: $media->is_main,
            createdAt: $media->created_at !== null ? $media->created_at->format('Y-m-d H:i') : '',
        );
    }
}
