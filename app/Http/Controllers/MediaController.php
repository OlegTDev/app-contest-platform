<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MediaController extends Controller
{
    public function index(Request $request, Contest $contest): Response
    {
        abort_unless($request->user()->id === $contest->user_id, 403);

        $media = $contest->media()->orderByDesc('created_at')->get()->map(fn ($m) => [
            'id' => $m->id,
            'file_name' => $m->file_name,
            'file_url' => $m->file_url,
            'file_type' => $m->file_type,
            'file_extension' => $m->file_extension,
            'file_size' => $m->file_size,
            'created_at' => $m->created_at->format('Y-m-d H:i'),
        ]);

        return Inertia::render('media/Index', [
            'contest' => [
                'id' => $contest->id,
                'title' => $contest->title,
            ],
            'media' => $media,
        ]);
    }

    public function store(Request $request, Contest $contest): JsonResponse
    {
        abort_unless($request->user()->id === $contest->user_id, 403);

        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:10240', // max 10MB per file
        ]);

        $uploaded = [];

        foreach ($request->file('files') as $file) {
            $path = $file->store('media/' . $contest->id, 'public');
            $extension = $file->getClientOriginalExtension();
            $mimeType = $file->getMimeType();

            $fileType = match (true) {
                str_starts_with($mimeType, 'image/') => 'image',
                str_starts_with($mimeType, 'video/') => 'video',
                str_starts_with($mimeType, 'audio/') => 'audio',
                $extension === 'pdf' => 'pdf',
                default => 'document',
            };

            $media = Media::create([
                'contest_id' => $contest->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $fileType,
                'file_extension' => $extension,
                'file_size' => $file->getSize(),
            ]);

            $uploaded[] = [
                'id' => $media->id,
                'file_name' => $media->file_name,
                'file_url' => $media->file_url,
                'file_type' => $media->file_type,
                'file_extension' => $media->file_extension,
                'file_size' => $media->file_size,
                'created_at' => $media->created_at->format('Y-m-d H:i'),
            ];
        }

        return response()->json([
            'message' => 'Files uploaded successfully.',
            'media' => $uploaded,
        ]);
    }

    public function destroy(Request $request, Contest $contest, Media $media): RedirectResponse
    {
        abort_unless($request->user()->id === $contest->user_id, 403);
        abort_unless($media->contest_id === $contest->id, 404);

        Storage::disk('public')->delete($media->file_path);
        $media->delete();

        return back()->with('success', 'Media deleted successfully.');
    }
}
