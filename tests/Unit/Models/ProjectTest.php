<?php

use App\Models\Contest;
use App\Models\Project;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->contest = Contest::factory()->create(['user_id' => $this->user->id]);
});

describe('Project model', function () {
    it('can create a project with all attributes', function () {
        $project = Project::factory()->create([
            'contest_id' => $this->contest->id,
            'user_id' => $this->user->id,
            'title' => 'My Project',
            'description' => 'Project description',
            'fields_data' => ['key' => 'value'],
        ]);

        expect($project->contest_id)->toBe($this->contest->id)
            ->and($project->user_id)->toBe($this->user->id)
            ->and($project->title)->toBe('My Project')
            ->and($project->description)->toBe('Project description')
            ->and($project->fields_data)->toBe(['key' => 'value']);
    });

    it('uses the projects table', function () {
        $project = new Project();

        expect($project->getTable())->toBe('projects');
    });

    it('has correct fillable attributes', function () {
        $project = new Project();

        expect($project->getFillable())
            ->toBe(['contest_id', 'user_id', 'title', 'description', 'fields_data']);
    });

    it('casts fields_data as an array', function () {
        $data = ['steps' => ['one', 'two'], 'nested' => ['key' => 1]];

        $project = Project::factory()->create(['fields_data' => $data]);

        expect($project->fields_data)->toBeArray()
            ->and($project->fields_data)->toBe($data);
    });

    it('persists fields_data and reads it back', function () {
        $data = ['a' => 1, 'b' => ['c' => true]];

        $project = Project::factory()->create(['fields_data' => $data]);

        expect($project->fresh()->fields_data)->toBe($data);
    });

    describe('relationship with contest', function () {
        it('is owned by the contest it belongs to', function () {
            $project = Project::factory()->create(['contest_id' => $this->contest->id]);

            expect($this->contest->projects()->whereKey($project->id)->exists())->toBeTrue()
                ->and($project->contest_id)->toBe($this->contest->id);
        });

        it('is included in the contest projects collection', function () {
            $project = Project::factory()->create(['contest_id' => $this->contest->id]);

            expect($this->contest->fresh('projects')->projects->pluck('id')->contains($project->id))->toBeTrue();
        });
    });

    describe('edge cases', function () {
        it('handles a null description', function () {
            $project = Project::factory()->create(['description' => null]);

            expect($project->description)->toBeNull();
        });

        it('handles an empty fields_data array', function () {
            $project = Project::factory()->create(['fields_data' => []]);

            expect($project->fields_data)->toBeArray()->toBeEmpty();
        });

        it('handles deeply nested fields_data', function () {
            $data = ['level1' => ['level2' => ['level3' => ['value' => true]]]];

            $project = Project::factory()->create(['fields_data' => $data]);

            expect($project->fresh()->fields_data)->toBe($data);
        });
    });
});
