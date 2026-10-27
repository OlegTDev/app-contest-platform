<?php

namespace App\Observers;

use App\Models\Contest;
use App\Models\User;
use Illuminate\Support\Str;

class ContestObserver
{
    public function creating(Contest $contest): void
    {
        $user = auth()->user();

        if ($user instanceof User) {
            $contest->user_id = $user->id;
        }

        $this->generateSlug($contest);
    }

    public function updating(Contest $contest): void
    {
        // Генерируем slug только если он был пустым до начала редактирования
        // и пользователь его не заполнил явно
        $originalSlug = $contest->getOriginal('slug');

        if (! empty($originalSlug)) {
            // У пользователя уже был slug — не трогаем
            return;
        }

        $this->generateSlug($contest);
    }

    private function generateSlug(Contest $contest): void
    {
        if (! empty($contest->slug) || empty($contest->title)) {
            return;
        }

        $baseSlug = Str::slug($contest->title, '-');
        $slug = $baseSlug;
        $counter = 1;

        while ($this->slugExists($slug, $contest->exists ? $contest->id : null)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $contest->slug = $slug;
    }

    private function slugExists(string $slug, ?int $excludeId): bool
    {
        $query = Contest::where('slug', $slug);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
