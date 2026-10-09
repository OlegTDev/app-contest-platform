<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Media;
use Illuminate\Support\Collection;

final readonly class MediaDTO
{
    public function __construct(
        public int $id,
        public string $fileName,
        public string $fileUrl,
        public string $fileType,
        public string $fileExtension,
        public ?int $fileSize,
        public bool $isMain,
        public string $createdAt,
    ) {}

    /**
     * Map a collection of media to DTOs.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, Media>  $media
     * @return Collection<int, self>
     */
    public static function collection(Collection $media): Collection
    {
        return $media->map(fn (Media $m): self => self::from($m));
    }

    public static function from(Media $media): self
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
