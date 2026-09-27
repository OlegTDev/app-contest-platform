<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
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
    protected $casts = [
        'fields_data' => 'array',
    ];
}
