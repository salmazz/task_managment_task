<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    Use HasFactory , SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'status',
        'assignee_id',
        'created_by',
        'due_date',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
    ];

    public function dependencies()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'dependency_task_id');
    }

    public function dependents()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'dependency_task_id', 'task_id');
    }
}
