<?php

namespace App\Models;

use App\Enums\ContestType;
use App\Observers\ContestObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $title
 * @property mixed $project_schema
 * @property \Illuminate\Database\Eloquent\Collection<int, Project> $projects
 */
#[Fillable(['title', 'type', 'project_schema'])]
#[ObservedBy(ContestObserver::class)]
class Contest extends Model
{
    protected $casts = [
        'project_schema' => 'array',
        'type' => ContestType::class,
    ];

    /**
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
