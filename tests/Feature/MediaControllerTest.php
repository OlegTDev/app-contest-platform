<?php

use App\Enums\ContestType;
use App\Models\Contest;
use App\Models\Media;
use App\Models\QuizEntry;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->moderator = User::factory()->create(['role' => 'moderator']);
    $this->user = User::factory()->create(['role' => 'user']);
    $this->contest = Contest::factory()->create(['type' => ContestType::VOTING]);
});

describe('MediaController', function () {
    describe('index', function () {
        it('renders media index for admin', function () {
            $response = $this->actingAs($this->admin)->get("/admin/contests/{$this->contest->id}/media");

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('media/Index'));
        });

        it('renders media index for moderator', function () {
            $response = $this->actingAs($this->moderator)->get("/admin/contests/{$this->contest->id}/media");

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('media/Index'));
        });

        it('rejects regular user from media index', function () {
            $response = $this->actingAs($this->user)->get("/admin/contests/{$this->contest->id}/media");

            $response->assertStatus(403);
        });

        it('includes media list in response', function () {
            $media = Media::factory()->create(['contest_id' => $this->contest->id]);

            $response = $this->actingAs($this->admin)->get("/admin/contests/{$this->contest->id}/media");

            $response->assertInertia(fn ($page) => $page->has('media', 1));
        });
    });

    describe('store', function () {
        beforeEach(function () {
            Storage::fake('public');
        });

        it('uploads files to contest', function () {
            $file = UploadedFile::fake()->image('photo.jpg', 100);

            $response = $this->actingAs($this->admin)->post("/admin/contests/{$this->contest->id}/media", [
                'files' => [$file],
            ]);

            $response->assertRedirect();
            expect(Media::where('contest_id', $this->contest->id)->count())->toBe(1);
        });

        it('rejects regular user from uploading', function () {
            $file = UploadedFile::fake()->image('photo.jpg', 100);

            $response = $this->actingAs($this->user)->post("/admin/contests/{$this->contest->id}/media", [
                'files' => [$file],
            ]);

            $response->assertStatus(403);
        });

        it('validates files are required', function () {
            $response = $this->actingAs($this->admin)->post("/admin/contests/{$this->contest->id}/media", []);

            $response->assertSessionHasErrors('files');
        });
    });

    describe('entryIndex', function () {
        it('renders entry media index for voting entry', function () {
            $entry = \App\Models\ContestEntry::factory()->create(['contest_id' => $this->contest->id]);

            $response = $this->actingAs($this->admin)->get("/admin/contests/{$this->contest->id}/entries/{$entry->id}/media");

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('media/EntryIndex'));
        });

        it('renders entry media index for quiz entry', function () {
            $contest = Contest::factory()->create(['type' => ContestType::QUIZ]);
            $entry = QuizEntry::factory()->create(['contest_id' => $contest->id]);

            $response = $this->actingAs($this->admin)->get("/admin/contests/{$contest->id}/entries/{$entry->id}/media");

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('media/EntryIndex'));
        });

        it('returns 404 for entry not belonging to contest', function () {
            $otherContest = Contest::factory()->create(['type' => ContestType::VOTING]);
            $otherEntry = \App\Models\ContestEntry::factory()->create(['contest_id' => $otherContest->id]);

            $response = $this->actingAs($this->admin)->get("/admin/contests/{$this->contest->id}/entries/{$otherEntry->id}/media");

            $response->assertStatus(404);
        });
    });

    describe('entryStore', function () {
        beforeEach(function () {
            Storage::fake('public');
        });

        it('uploads files to voting entry', function () {
            $entry = \App\Models\ContestEntry::factory()->create(['contest_id' => $this->contest->id]);
            $file = UploadedFile::fake()->image('photo.jpg', 100);

            $response = $this->actingAs($this->admin)->post("/admin/contests/{$this->contest->id}/entries/{$entry->id}/media", [
                'files' => [$file],
            ]);

            $response->assertRedirect();
            expect(Media::where('entry_id', $entry->id)->count())->toBe(1);
        });

        it('rejects regular user from uploading to entry', function () {
            $entry = \App\Models\ContestEntry::factory()->create(['contest_id' => $this->contest->id]);
            $file = UploadedFile::fake()->image('photo.jpg', 100);

            $response = $this->actingAs($this->user)->post("/admin/contests/{$this->contest->id}/entries/{$entry->id}/media", [
                'files' => [$file],
            ]);

            $response->assertStatus(403);
        });
    });

    describe('destroy', function () {
        beforeEach(function () {
            Storage::fake('public');
        });

        it('deletes media from contest', function () {
            $media = Media::factory()->create([
                'contest_id' => $this->contest->id,
                'file_path' => 'contests/' . $this->contest->id . '/photo.jpg',
                'file_name' => 'photo.jpg',
            ]);

            $response = $this->actingAs($this->admin)->delete("/admin/contests/{$this->contest->id}/media/{$media->id}");

            $response->assertRedirect();
            expect(Media::where('id', $media->id)->exists())->toBeFalse();
        });

        it('rejects regular user from deleting media', function () {
            $media = Media::factory()->create(['contest_id' => $this->contest->id]);

            $response = $this->actingAs($this->user)->delete("/admin/contests/{$this->contest->id}/media/{$media->id}");

            $response->assertStatus(403);
        });

        it('returns 404 for media not belonging to contest', function () {
            $otherContest = Contest::factory()->create(['type' => ContestType::VOTING]);
            $otherMedia = Media::factory()->create(['contest_id' => $otherContest->id]);

            $response = $this->actingAs($this->admin)->delete("/admin/contests/{$this->contest->id}/media/{$otherMedia->id}");

            $response->assertStatus(404);
        });
    });

    describe('entryDestroy', function () {
        beforeEach(function () {
            Storage::fake('public');
        });

        it('deletes media from voting entry', function () {
            $entry = \App\Models\ContestEntry::factory()->create(['contest_id' => $this->contest->id]);
            $media = Media::factory()->create([
                'contest_id' => $this->contest->id,
                'entry_id' => $entry->id,
                'entry_type' => \App\Models\ContestEntry::class,
                'file_path' => 'entries/' . $entry->id . '/photo.jpg',
                'file_name' => 'photo.jpg',
            ]);

            $response = $this->actingAs($this->admin)->delete("/admin/contests/{$this->contest->id}/entries/{$entry->id}/media/{$media->id}");

            $response->assertRedirect();
            expect(Media::where('id', $media->id)->exists())->toBeFalse();
        });

        it('rejects regular user from deleting entry media', function () {
            $entry = \App\Models\ContestEntry::factory()->create(['contest_id' => $this->contest->id]);
            $media = Media::factory()->create([
                'contest_id' => $this->contest->id,
                'entry_id' => $entry->id,
                'entry_type' => \App\Models\ContestEntry::class,
                'file_path' => 'entries/' . $entry->id . '/photo.jpg',
                'file_name' => 'photo.jpg',
            ]);

            $response = $this->actingAs($this->user)->delete("/admin/contests/{$this->contest->id}/entries/{$entry->id}/media/{$media->id}");

            $response->assertStatus(403);
        });
    });

    describe('toggleMain', function () {
        it('toggles main media for voting entry', function () {
            $entry = \App\Models\ContestEntry::factory()->create(['contest_id' => $this->contest->id]);
            $media = Media::factory()->create([
                'contest_id' => $this->contest->id,
                'entry_id' => $entry->id,
                'entry_type' => \App\Models\ContestEntry::class,
                'is_main' => false,
            ]);

            $response = $this->actingAs($this->admin)->post("/admin/contests/{$this->contest->id}/entries/{$entry->id}/media/{$media->id}/main");

            $response->assertRedirect();
            expect($media->fresh()->is_main)->toBeTrue();
        });

        it('unsets previous main when setting new one', function () {
            $entry = \App\Models\ContestEntry::factory()->create(['contest_id' => $this->contest->id]);
            $mainMedia = Media::factory()->create([
                'contest_id' => $this->contest->id,
                'entry_id' => $entry->id,
                'entry_type' => \App\Models\ContestEntry::class,
                'is_main' => true,
            ]);
            $otherMedia = Media::factory()->create([
                'contest_id' => $this->contest->id,
                'entry_id' => $entry->id,
                'entry_type' => \App\Models\ContestEntry::class,
                'is_main' => false,
            ]);

            $response = $this->actingAs($this->admin)->post("/admin/contests/{$this->contest->id}/entries/{$entry->id}/media/{$otherMedia->id}/main");

            $response->assertRedirect();
            expect($mainMedia->fresh()->is_main)->toBeFalse();
            expect($otherMedia->fresh()->is_main)->toBeTrue();
        });

        it('rejects regular user from toggling main', function () {
            $entry = \App\Models\ContestEntry::factory()->create(['contest_id' => $this->contest->id]);
            $media = Media::factory()->create([
                'contest_id' => $this->contest->id,
                'entry_id' => $entry->id,
                'entry_type' => \App\Models\ContestEntry::class,
            ]);

            $response = $this->actingAs($this->user)->post("/admin/contests/{$this->contest->id}/entries/{$entry->id}/media/{$media->id}/main");

            $response->assertStatus(403);
        });
    });
});
