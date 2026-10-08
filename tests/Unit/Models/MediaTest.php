<?php

use App\Models\Contest;
use App\Models\ContestEntry;
use App\Models\Media;
use App\Models\QuizEntry;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    $this->contest = Contest::factory()->create();
    $this->entry = ContestEntry::factory()->create(['contest_id' => $this->contest->id]);
    $this->quizEntry = QuizEntry::factory()->create(['contest_id' => $this->contest->id]);
});

describe('Media model', function () {
    it('can create media with all attributes', function () {
        $media = Media::factory()->create([
            'contest_id' => $this->contest->id,
            'file_name' => 'photo.jpg',
            'file_path' => 'media/photo.jpg',
            'file_type' => 'image/jpeg',
            'file_extension' => 'jpg',
            'file_size' => 102400,
            'is_main' => true,
        ]);

        expect($media->file_name)->toBe('photo.jpg')
            ->and($media->file_path)->toBe('media/photo.jpg')
            ->and($media->file_type)->toBe('image/jpeg')
            ->and($media->file_extension)->toBe('jpg')
            ->and($media->file_size)->toBe(102400)
            ->and($media->is_main)->toBeTrue();
    });

    it('has correct fillable attributes', function () {
        $media = new Media();

        expect($media->getFillable())
            ->toBe(['contest_id', 'entry_id', 'entry_type', 'file_name', 'file_path', 'file_type', 'file_extension', 'file_size', 'is_main']);
    });

    it('casts file_size as integer', function () {
        $media = Media::factory()->create(['file_size' => '524288']);

        expect($media->file_size)->toBeInt()
            ->and($media->file_size)->toBe(524288);
    });

    it('casts is_main as boolean', function () {
        $media = Media::factory()->create(['is_main' => '1']);

        expect($media->is_main)->toBeBool()
            ->and($media->is_main)->toBeTrue();
    });

    describe('getFileUrlAttribute()', function () {
        beforeEach(function () {
            URL::forceRootUrl('http://localhost');
        });

        it('generates correct file URL', function () {
            $media = Media::factory()->create(['file_path' => 'media/test.pdf']);

            expect($media->file_url)->toBe('http://localhost/storage/media/test.pdf');
        });

        it('handles nested file paths', function () {
            $media = Media::factory()->create(['file_path' => 'contests/1/files/report.pdf']);

            expect($media->file_url)->toBe('http://localhost/storage/contests/1/files/report.pdf');
        });

        it('handles empty file path', function () {
            $media = Media::factory()->create(['file_path' => '']);

            expect($media->file_url)->toBe('http://localhost/storage');
        });
    });

    describe('polymorphic entry relationship', function () {
        it('can belong to a contest entry', function () {
            $media = Media::factory()->create([
                'entry_id' => $this->entry->id,
                'entry_type' => ContestEntry::class,
            ]);

            expect($media->entry)->toBeInstanceOf(ContestEntry::class)
                ->and($media->entry->id)->toBe($this->entry->id);
        });

        it('can belong to a quiz entry', function () {
            $media = Media::factory()->create([
                'entry_id' => $this->quizEntry->id,
                'entry_type' => QuizEntry::class,
            ]);

            expect($media->entry)->toBeInstanceOf(QuizEntry::class)
                ->and($media->entry->id)->toBe($this->quizEntry->id);
        });
    });

    describe('contest relationship', function () {
        it('belongs to a contest', function () {
            $media = Media::factory()->create(['contest_id' => $this->contest->id]);

            expect($media->fresh('contest')->contest->id)->toBe($this->contest->id);
        });
    });

    describe('edge cases', function () {
        it('handles null file_extension', function () {
            $media = Media::factory()->create(['file_extension' => null]);
            expect($media->file_extension)->toBeNull();
        });

        it('handles null file_size', function () {
            $media = Media::factory()->create(['file_size' => null]);
            expect($media->file_size)->toBeNull();
        });

        it('handles zero file_size', function () {
            $media = Media::factory()->create(['file_size' => 0]);
            expect($media->file_size)->toBe(0);
        });

        it('handles large file_size', function () {
            $media = Media::factory()->create(['file_size' => 1073741824]); // 1 GB
            expect($media->file_size)->toBe(1073741824);
        });
    });
});
