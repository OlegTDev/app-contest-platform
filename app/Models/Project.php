<?php

namespace App\Models;

use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $contest_id
 * @property int $user_id
 * @property string $title
 * @property string|null $description
 * @property mixed $fields_data
 */
#[Fillable(['contest_id', 'user_id', 'title', 'description', 'fields_data'])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    protected $casts = [
        'fields_data' => 'array',
    ];
}
