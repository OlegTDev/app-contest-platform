<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\ContestEntry;
use App\Models\Media;
use App\Models\QuizEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MediaController extends Controller
{
    public function index(Request $request, Contest $contest): Response
    {
        $this->canManageContest($contest);

        $media = $contest->media()->orderByDesc('created_at')->get()->map(fn ($m) => [
            'id' => $m->id,
            'file_name' => $m->file_name,
            'file_url' => $m->file_url,
            'file_type' => $m->file_type,
            'file_extension' => $m->file_extension,
            'file_size' => $m->file_size,
            'is_main' => $m->is_main,
            'created_at' => $m->created_at?->format('Y-m-d H:i') ?? '',
        ]);

        return Inertia::render('media/Index', [
            'contest' => [
                'id' => $contest->id,
                'title' => $contest->title,
            ],
            'media' => $media,
        ]);
    }

    public function store(Request $request, Contest $contest): RedirectResponse
    {
        $this->canManageContest($contest);

        /** @var int $maxSize */
        $maxSize = config('media.max_upload_size');

        $request->validate([
            'files' => 'required|array',
            'files.*' => "file|max:{$maxSize}",
            'entry_id' => 'nullable|integer',
            'entry_type' => 'nullable|string',
        ]);

        $entryId = $request->input('entry_id');
        $entryType = $request->input('entry_type');

        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension() ?: 'bin';
            /** @var string $mimeType */
            $mimeType = $file->getMimeType();

            $fileType = match (true) {
                str_starts_with($mimeType, 'image/') => 'image',
                str_starts_with($mimeType, 'video/') => 'video',
                str_starts_with($mimeType, 'audio/') => 'audio',
                $extension === 'pdf' => 'pdf',
                default => 'document',
            };

            $uniqueName = uniqid().'.'.$extension;
            $path = $file->storeAs('media/'.$contest->id, $uniqueName, 'public');

            $thumbnailPath = null;
            if ($fileType === 'image') {
                $thumbnailPath = $this->generateThumbnail($file, $path, $contest->id);
            }

            Media::create([
                'contest_id' => $contest->id,
                'entry_id' => $entryId,
                'entry_type' => $entryType ?: 'Contest',
                'file_name' => $originalName,
                'file_path' => $path,
                'thumbnail_path' => $thumbnailPath,
                'file_type' => $fileType,
                'file_extension' => $extension,
                'file_size' => $file->getSize(),
            ]);
        }

        return redirect()->back()->with('success', 'Файлы успешно загружены.');
    }

    public function entryIndex(Request $request, Contest $contest, string $entry): Response
    {
        $this->canManageContest($contest);

        /** @var ContestEntry|QuizEntry|null $entryModel */
        $entryModel = ContestEntry::where('id', $entry)->where('contest_id', $contest->id)->first()
            ?? QuizEntry::where('id', $entry)->where('contest_id', $contest->id)->first();

        if ($entryModel === null) {
            abort(404);
        }

        $media = $entryModel->media()->orderByDesc('created_at')->get()->map(fn ($m) => [
            'id' => $m->id,
            'file_name' => $m->file_name,
            'file_url' => $m->file_url,
            'file_type' => $m->file_type,
            'file_extension' => $m->file_extension,
            'file_size' => $m->file_size,
            'is_main' => $m->is_main,
            'created_at' => $m->created_at?->format('Y-m-d H:i') ?? '',
        ]);

        return Inertia::render('media/EntryIndex', [
            'contest' => [
                'id' => $contest->id,
                'title' => $contest->title,
            ],
            'entry' => [
                'id' => $entryModel->id,
                'title' => $entryModel->title,
            ],
            'media' => $media,
        ]);
    }

    public function entryStore(Request $request, Contest $contest, string $entry): RedirectResponse
    {
        $this->canManageContest($contest);

        /** @var ContestEntry|QuizEntry|null $entryModel */
        $entryModel = ContestEntry::where('id', $entry)->where('contest_id', $contest->id)->first()
            ?? QuizEntry::where('id', $entry)->where('contest_id', $contest->id)->first();

        if ($entryModel === null) {
            abort(404);
        }

        /** @var int $maxSize */
        $maxSize = config('media.max_upload_size');

        $request->validate([
            'files' => 'required|array',
            'files.*' => "file|max:{$maxSize}",
        ]);

        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension() ?: 'bin';
            /** @var string $mimeType */
            $mimeType = $file->getMimeType();

            $fileType = match (true) {
                str_starts_with($mimeType, 'image/') => 'image',
                str_starts_with($mimeType, 'video/') => 'video',
                str_starts_with($mimeType, 'audio/') => 'audio',
                $extension === 'pdf' => 'pdf',
                default => 'document',
            };

            $uniqueName = uniqid().'.'.$extension;
            $path = $file->storeAs('media/'.$contest->id, $uniqueName, 'public');

            $thumbnailPath = null;
            if ($fileType === 'image') {
                $thumbnailPath = $this->generateThumbnail($file, $path, $contest->id);
            }

            Media::create([
                'contest_id' => $contest->id,
                'entry_id' => $entryModel->id,
                'entry_type' => get_class($entryModel),
                'file_name' => $originalName,
                'file_path' => $path,
                'thumbnail_path' => $thumbnailPath,
                'file_type' => $fileType,
                'file_extension' => $extension,
                'file_size' => $file->getSize(),
            ]);
        }

        return redirect()->back()->with('success', 'Файлы успешно загружены.');
    }

    public function destroy(Request $request, Contest $contest, Media $media): RedirectResponse
    {
        $this->canManageContest($contest);
        abort_unless($media->contest_id === $contest->id, 404);

        Storage::disk('public')->delete($media->file_path);
        if ($media->thumbnail_path) {
            Storage::disk('public')->delete($media->thumbnail_path);
        }
        $media->delete();

        return back()->with('success', 'Media deleted successfully.');
    }

    public function entryDestroy(Request $request, Contest $contest, string $entry, Media $media): RedirectResponse
    {
        $this->canManageContest($contest);
        abort_unless($media->contest_id === $contest->id, 404);

        /** @var ContestEntry|QuizEntry|null $entryModel */
        $entryModel = ContestEntry::where('id', $entry)->where('contest_id', $contest->id)->first()
            ?? QuizEntry::where('id', $entry)->where('contest_id', $contest->id)->first();

        if ($entryModel === null) {
            abort(404);
        }
        abort_unless($media->entry_id === $entryModel->id, 404);

        Storage::disk('public')->delete($media->file_path);
        if ($media->thumbnail_path) {
            Storage::disk('public')->delete($media->thumbnail_path);
        }
        $media->delete();

        return back()->with('success', 'Media deleted successfully.');
    }

    public function toggleMain(Request $request, Contest $contest, string $entry, Media $media): RedirectResponse
    {
        $this->canManageContest($contest);
        abort_unless($media->contest_id === $contest->id, 404);

        /** @var ContestEntry|QuizEntry|null $entryModel */
        $entryModel = ContestEntry::where('id', $entry)->where('contest_id', $contest->id)->first()
            ?? QuizEntry::where('id', $entry)->where('contest_id', $contest->id)->first();

        if ($entryModel === null) {
            abort(404);
        }
        abort_unless($media->entry_id === $entryModel->id, 404);

        // If setting this file as main, unset the previous main file of this entry.
        if (! $media->is_main) {
            Media::where('entry_id', $entryModel->id)
                ->where('entry_type', get_class($entryModel))
                ->where('is_main', true)
                ->update(['is_main' => false]);
        }

        $media->update(['is_main' => ! $media->is_main]);

        return back()->with('success', $media->is_main ? 'Файл сделан главным.' : 'Файл больше не главный.');
    }

    /**
     * Generate a thumbnail for an uploaded image using native GD.
     */
    private function generateThumbnail(\Illuminate\Http\UploadedFile $file, string $originalPath, int $contestId): ?string
    {
        $source = $file->getRealPath();
        $meta = getimagesize($source);

        if ($meta === false) {
            return null;
        }

        $width = $meta[0];
        $height = $meta[1];
        $type = $meta[2];

        $sourceImage = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($source),
            IMAGETYPE_PNG => imagecreatefrompng($source),
            IMAGETYPE_WEBP => imagecreatefromwebp($source),
            IMAGETYPE_GIF => imagecreatefromgif($source),
            default => null,
        };

        if ($sourceImage === false) {
            return null;
        }

        $targetSize = 500;

        // Preserve aspect ratio: fit within 500x500 box
        if ($width > $height) {
            $newWidth = min($width, $targetSize);
            $newHeight = (int) round($height * ($newWidth / $width));
        } else {
            $newHeight = min($height, $targetSize);
            $newWidth = (int) round($width * ($newHeight / $height));
        }

        $thumb = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($thumb, true);
        imagesavealpha($thumb, true);

        if ($type === IMAGETYPE_PNG) {
            $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
            imagefilledrectangle($thumb, 0, 0, $newWidth, $newHeight, $transparent);
        } elseif ($type === IMAGETYPE_JPEG || $type === IMAGETYPE_WEBP) {
            $white = imagecolorallocate($thumb, 255, 255, 255);
            imagefilledrectangle($thumb, 0, 0, $newWidth, $newHeight, $white);
        }

        // Two-pass resize for better quality
        $intermediate = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($intermediate, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagecopyresampled($thumb, $intermediate, 0, 0, 0, 0, $newWidth, $newHeight, $newWidth, $newHeight);
        imagedestroy($intermediate);

        imagedestroy($sourceImage);

        $thumbName = pathinfo($originalPath, PATHINFO_FILENAME).'_thumb.jpg';
        $thumbPath = 'media/'.$contestId.'/thumbs/'.$thumbName;

        $thumbDir = dirname(Storage::disk('public')->path($thumbPath));
        if (! is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }

        imagejpeg($thumb, Storage::disk('public')->path($thumbPath), 95);
        imagedestroy($thumb);

        return $thumbPath;
    }
}
