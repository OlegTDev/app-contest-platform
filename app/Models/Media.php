<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['contest_id', 'entry_id', 'entry_type', 'file_name', 'file_path', 'file_type', 'file_extension', 'file_size', 'is_main'])]
class Media extends Model
{
    protected $casts = [
        'file_size' => 'integer',
        'is_main' => 'boolean',
    ];

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    public function entry()
    {
        return $this->morphTo(__FUNCTION__, 'entry_type', 'entry_id');
    }

    public function getFileUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
