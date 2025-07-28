<?php

namespace App\Services\Task;

use App\Enums\TaskStatus;
use App\Events\TaskCompleted;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class TaskService
{
    public function __construct() {}

    public function listWithFilters(array $filters,User $user)
    {
        $query = Task::query();

        // Restrict normal users to only their tasks
        if (!$user->isManager()) {
            $query->where('assignee_id', $user->id);
        } else {
            //  Only managers can filter by assignee
            if (!empty($filters['assignee_id'])) {
                $query->where('assignee_id', $filters['assignee_id']);
            }
        }

        // 🔍 Apply common filters for all roles
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['due_date_from'])) {
            $query->whereDate('due_date', '>=', $filters['due_date_from']);
        }

        if (!empty($filters['due_date_to'])) {
            $query->whereDate('due_date', '<=', $filters['due_date_to']);
        }

        return $query->with('dependencies')->get();
    }

    public function create(array $data): Task
    {
        return DB::transaction(function () use ($data) {
            $task = Task::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'assignee_id' => $data['assignee_id'],
                'created_by' => Auth::id(),
                'due_date' => $data['due_date'],
                'status' => 'pending',
            ]);

            // Attach dependencies if any
            if (!empty($data['dependencies'])) {
                $task->dependencies()->sync($data['dependencies']);
            }

            return $task;
        });
    }

    public function update(Task $task, array $data): Task
    {
        $user = Auth::user();

        $isManager = $user->isManager();
        $isAssignee = $user->id === $task->assignee_id;

        if ($isManager) {
            if (isset($data['status']) && $data['status'] === TaskStatus::Completed->value) {
                $this->ensureDependenciesCompleted($task);
            }

            $task->update([
                'title'       => $data['title'] ?? $task->title,
                'description' => $data['description'] ?? $task->description,
                'due_date'    => $data['due_date'] ?? $task->due_date,
                'assignee_id' => $data['assignee_id'] ?? $task->assignee_id,
                'status'      => $data['status'] ?? $task->status,
            ]);


            if ($task->status === TaskStatus::Completed->value) {
                event(new TaskCompleted($task));
            }

            return $task;
        }

        if ($isAssignee && isset($data['status'])) {
            if ($data['status'] === TaskStatus::Completed->value) {
                $this->ensureDependenciesCompleted($task);
            }

            $task->status = $data['status'];
            $task->save();

            if ($task->status === TaskStatus::Completed->value) {
                event(new TaskCompleted($task));
            }

            return $task;
        }

        throw ValidationException::withMessages([
            'unauthorized' => 'You are not authorized to update this task.',
        ]);
    }

    protected function ensureDependenciesCompleted(Task $task): void
    {
        $incompleteDeps = $task->dependencies()
            ->where('tasks.status', '!=', TaskStatus::Completed->value)
            ->count();

        if ($incompleteDeps > 0) {
            throw ValidationException::withMessages([
                'status' => 'Cannot complete task with incomplete dependencies.',
            ]);
        }
    }
    public function getByIdWithDependencies(int $id): Task
    {
        return Task::with('dependencies')->findOrFail($id);
    }

    public function destroy($task): ?bool
    {
        return $task->delete();
    }

    public function addDependencies(Task $task, array $dependencyIds): Task
    {
        foreach ($dependencyIds as $dependencyId) {
            if ($task->id === $dependencyId) {
                throw ValidationException::withMessages([
                    'dependencies' => 'Task cannot depend on itself.',
                ]);
            }

            $exists = Task::where('id', $dependencyId)->exists();
            if (!$exists) {
                throw ValidationException::withMessages([
                    'dependencies' => "Task with ID {$dependencyId} does not exist. Please assign a valid task.",
                ]);
            }

            if ($task->dependencies->contains($dependencyId)) {
                throw ValidationException::withMessages([
                    'dependencies' => "Task already depends on task ID: $dependencyId.",
                ]);
            }

            if ($this->hasCircularDependency($task, $dependencyId)) {
                throw ValidationException::withMessages([
                    'dependencies' => "Circular dependency detected with task ID: $dependencyId.",
                ]);
            }
        }

        $task->dependencies()->syncWithoutDetaching($dependencyIds);

        return $task->load('dependencies');
    }


    protected function hasCircularDependency(Task $task, int $newDependencyId): bool
    {
        $visited = [];

        $check = function ($currentId) use ($task, &$check, &$visited) {
            if (in_array($currentId, $visited)) return false;

            $visited[] = $currentId;

            $dependencies = Task::find($currentId)?->dependencies()->pluck('tasks.id')->toArray() ?? [];

            foreach ($dependencies as $depId) {
                if ($depId === $task->id || $check($depId)) {
                    return true;
                }
            }

            return false;
        };

        return $check($newDependencyId);
    }
}
