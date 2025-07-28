<?php

namespace App\Repositories\Eloquent;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;

class TaskRepository implements TaskRepositoryInterface
{
    public function getAll(array $filters): \Illuminate\Support\Collection
    {
        return Task::with(['dependencies', 'assignee'])
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['due_from']), fn($q) => $q->where('due_date', '>=', $filters['due_from']))
            ->when(isset($filters['due_to']), fn($q) => $q->where('due_date', '<=', $filters['due_to']))
            ->when(isset($filters['assignee_id']), fn($q) => $q->where('assignee_id', $filters['assignee_id']))
            ->get();
    }

    public function find(int $id): ?Task
    {
        return Task::with(['dependencies', 'assignee'])->find($id);
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task;
    }

    public function updateStatus(Task $task, string $status): Task
    {
        $task->update(['status' => $status]);
        return $task;
    }

    public function attachDependencies(Task $task, array $dependencies): void
    {
        $task->dependencies()->syncWithoutDetaching($dependencies);
    }
}
