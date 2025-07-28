<?php

namespace App\Repositories\Contracts;

use App\Models\Task;

interface TaskRepositoryInterface
{
    public function getAll(array $filters): \Illuminate\Support\Collection;

    public function find(int $id): ?Task;

    public function create(array $data): Task;

    public function update(Task $task, array $data): Task;

    public function updateStatus(Task $task, string $status): Task;

    public function attachDependencies(Task $task, array $dependencies): void;
}
