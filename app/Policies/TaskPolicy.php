<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function view(User $user, Task $task): bool
    {
        return $user->isManager() || $task->assigned_to === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Task $task): bool
    {
        return $user->isManager();
    }

    public function updateStatus(User $user, Task $task): bool
    {
        return $task->assigned_to === $user->id;
    }

    public function addDependencies(User $user, Task $task): bool
    {
        return $user->isManager();
    }
}
