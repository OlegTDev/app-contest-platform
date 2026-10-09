<?php

namespace App\Models;

use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int|null $contest_id
 * @property int|null $entry_id
 * @property string|null $entry_type
 * @property string $file_name
 * @property string $file_path
 * @property string $file_type
 * @property string $file_extension
 * @property int $file_size
 * @property bool $is_main
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $file_url
 */
#[Fillable(['contest_id', 'entry_id', 'entry_type', 'file_name', 'file_path', 'file_type', 'file_extension', 'file_size', 'is_main'])]
class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    protected $casts = [
        'file_size' => 'integer',
        'is_main' => 'boolean',
    ];

    /**
     * @return BelongsTo<Contest, $this>
     */
    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function entry(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'entry_type', 'entry_id');
    }

    public function getFileUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
