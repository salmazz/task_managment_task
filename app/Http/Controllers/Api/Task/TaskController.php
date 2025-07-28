<?php

namespace App\Http\Controllers\Api\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\AttachDependencyRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Task;
use App\Services\Task\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(protected TaskService $taskService) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'due_date_from', 'due_date_to', 'assignee_id']);
        $user = $request->user();

        $tasks = $this->taskService->listWithFilters($filters, $user);

        return response()->json([
            'message' => 'Tasks retrieved successfully',
            'data'    => $tasks,
        ]);
    }

    public function store(StoreTaskRequest $request)
    {
        $task = $this->taskService->create($request->validated());

        return response()->json([
            'message' => 'Task created successfully',
            'data'    => $task,
        ], 201);
    }

    public function show($id)
    {
        $task = $this->taskService->getByIdWithDependencies($id);

        return response()->json([
            'message' => 'Task retrieved successfully',
            'data'    => $task,
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $data = $request->validated();
        $updatedTask = $this->taskService->update($task, $data);

        return response()->json([
            'message' => 'Task updated successfully',
            'data'    => $updatedTask,
        ]);
    }

    public function destroy(Task $task)
    {
        $this->taskService->destroy($task);

        return response()->json([
            'message' => 'Task deleted successfully',
            'data'    => null,
        ]);
    }

    public function addDependencies(AttachDependencyRequest $request, Task $task)
    {
        $validated = $request->validated();

        $updatedTask = $this->taskService->addDependencies($task, $validated['dependencies']);

        return response()->json([
            'message' => 'Dependencies added successfully.',
            'data' => $updatedTask
        ], 200);
    }
}
