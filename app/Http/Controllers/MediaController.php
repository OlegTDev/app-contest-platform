<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\Media;
use App\Services\MediaUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MediaController extends Controller
{
    public function index(Contest $contest): Response
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

    public function store(
        Request $request,
        Contest $contest,
        MediaUploadService $uploadService,
    ): RedirectResponse {
        $this->canManageContest($contest);

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
            $fileType = $uploadService->detectFileType($file);
            $attributes = $uploadService->uploadFile($file, $contest->id, $entryId, $entryType, $fileType);
            Media::create($attributes);
        }

        return redirect()->back()->with('success', 'Файлы успешно загружены.');
    }

    public function entryIndex(
        Request $request,
        Contest $contest,
        string $entry,
        MediaUploadService $uploadService,
    ): Response {
        $this->canManageContest($contest);

        $entryModel = $uploadService->resolveEntry($entry, $contest->id);

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

    public function entryStore(
        Request $request,
        Contest $contest,
        MediaUploadService $uploadService,
        string $entry,
    ): RedirectResponse {
        $this->canManageContest($contest);

        $entryModel = $uploadService->resolveEntry($entry, $contest->id);

        if ($entryModel === null) {
            abort(404);
        }

        $maxSize = config('media.max_upload_size');

        $request->validate([
            'files' => 'required|array',
            'files.*' => "file|max:{$maxSize}",
        ]);

        foreach ($request->file('files') as $file) {
            $fileType = $uploadService->detectFileType($file);
            $attributes = $uploadService->uploadFile(
                $file,
                $contest->id,
                $entryModel->id,
                get_class($entryModel),
                $fileType,
            );
            Media::create($attributes);
        }

        return redirect()->back()->with('success', 'Файлы успешно загружены.');
    }

    public function destroy(
        Request $request,
        Contest $contest,
        Media $media,
    ): RedirectResponse {
        $this->canManageContest($contest);
        abort_unless($media->contest_id === $contest->id, 404);

        Storage::disk('public')->delete($media->file_path);
        if ($media->thumbnail_path) {
            Storage::disk('public')->delete($media->thumbnail_path);
        }
        $media->delete();

        return back()->with('success', 'Media deleted successfully.');
    }

    public function entryDestroy(
        Request $request,
        Contest $contest,
        string $entry,
        Media $media,
        MediaUploadService $uploadService,
    ): RedirectResponse {
        $this->canManageContest($contest);
        abort_unless($media->contest_id === $contest->id, 404);

        $entryModel = $uploadService->resolveEntry($entry, $contest->id);

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

    public function toggleMain(
        Request $request,
        Contest $contest,
        string $entry,
        Media $media,
        MediaUploadService $uploadService,
    ): RedirectResponse {
        $this->canManageContest($contest);
        abort_unless($media->contest_id === $contest->id, 404);

        $entryModel = $uploadService->resolveEntry($entry, $contest->id);

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
}
