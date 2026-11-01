<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContestEntry;
use App\Models\QuizEntry;
use Illuminate\Http\UploadedFile;

/**
 * Handles media file upload logic shared between store and entryStore.
 */
class MediaUploadService
{
    public function __construct(
        private readonly ImageProcessor $imageProcessor,
    ) {}

    /**
     * Detect file type from mime and extension.
     */
    public function detectFileType(UploadedFile $file): string
    {
        /** @var string $mimeType */
        $mimeType = $file->getMimeType();
        $extension = $file->getClientOriginalExtension() ?: 'bin';

        return match (true) {
            str_starts_with($mimeType, 'image/') => 'image',
            str_starts_with($mimeType, 'video/') => 'video',
            str_starts_with($mimeType, 'audio/') => 'audio',
            $extension === 'pdf' => 'pdf',
            default => 'document',
        };
    }

    /**
     * Upload a single file and return its Media attributes.
     *
     * @return array<string, mixed>
     */
    public function uploadFile(
        UploadedFile $file,
        int $contestId,
        ?int $entryId,
        ?string $entryType,
        string $fileType,
    ): array {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension() ?: 'bin';

        $uniqueName = uniqid().'.'.$extension;
        $path = $file->storeAs('media/'.$contestId, $uniqueName, 'public');

        $thumbnailPath = null;
        if ($fileType === 'image') {
            $thumbnailPath = $this->imageProcessor->generate($file, $path, $contestId);
        }

        return [
            'contest_id' => $contestId,
            'entry_id' => $entryId,
            'entry_type' => $entryType ?: 'Contest',
            'file_name' => $originalName,
            'file_path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'file_type' => $fileType,
            'file_extension' => $extension,
            'file_size' => $file->getSize(),
        ];
    }

    /**
     * Resolve a model from ContestEntry or QuizEntry by id within a contest.
     */
    public function resolveEntry(string $entryId, int $contestId): ContestEntry|QuizEntry|null
    {
        return ContestEntry::where('id', $entryId)
            ->where('contest_id', $contestId)
            ->first()
            ?? QuizEntry::where('id', $entryId)
                ->where('contest_id', $contestId)
                ->first();
    }
}
