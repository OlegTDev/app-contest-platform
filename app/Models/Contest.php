<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property mixed $project_schema
 * @property \Illuminate\Database\Eloquent\Collection<int, Project> $projects
 */
#[Fillable(['title', 'project_schema'])]
class Contest extends Model
{
    protected $casts = [
        'project_schema' => 'array',
    ];

    /**
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
