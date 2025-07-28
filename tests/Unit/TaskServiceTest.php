<?php

namespace Tests\Unit;

use App\Enums\TaskStatus;
use App\Events\TaskCompleted;
use App\Models\Task;
use App\Models\User;
use App\Services\Task\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TaskService $taskService;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();


        $this->taskService = new TaskService();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_a_task_successfully()
    {
        $user = User::factory()->create();

        $data = [
            'title'       => 'Test Task',
            'description' => 'Some description',
            'due_date'    => now()->addDays(3)->toDateString(),
            'assignee_id' => $user->id,
        ];

        $this->actingAs($user);

        $task = $this->taskService->create($data);

        $this->assertDatabaseHas('tasks', [
            'title'       => 'Test Task',
            'assignee_id' => $user->id,
            'status'      => TaskStatus::Pending->value,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_status_to_completed_if_no_incomplete_dependencies()
    {
        Event::fake();

        $assignee = User::factory()->create();
        $manager = User::factory()->create(['role' => 'manager']); // ✅

        $this->actingAs($manager);

        $task = Task::factory()->create([
            'assignee_id' => $assignee->id,
            'created_by'  => $manager->id,
        ]);

        $updated = $this->taskService->update($task, ['status' => TaskStatus::Completed->value]);

        $this->assertEquals(TaskStatus::Completed->value, $updated->status->value);


        if ($task->status === TaskStatus::Completed->value) {
            event(new TaskCompleted($task));
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_prevents_completing_task_with_incomplete_dependencies()
    {
        $user = User::factory()->create(['role' => 'manager']);
        $this->actingAs($user);


        $dep = Task::factory()->create([
            'status' => TaskStatus::Pending->value,
            'created_by' => $user->id,
            'assignee_id' => $user->id,
        ]);

        $task = Task::factory()->create([
            'status' => TaskStatus::Pending->value,
            'created_by' => $user->id,
            'assignee_id' => $user->id,
        ]);

        $task->dependencies()->attach($dep->id);

        $this->expectExceptionMessage('Cannot complete task with incomplete dependencies.');

        $this->taskService->update($task, ['status' => TaskStatus::Completed->value]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_detects_circular_dependency()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $task1 = Task::factory()->create(['created_by' => $user->id, 'assignee_id' => $user->id]);
        $task2 = Task::factory()->create(['created_by' => $user->id, 'assignee_id' => $user->id]);


        $task1->dependencies()->attach($task2->id);

        // Try to make task2 depend on task1 → should throw
        $this->expectExceptionMessage("Circular dependency detected with task ID: {$task1->id}");

        $this->taskService->addDependencies($task2, [$task1->id]);
    }
}
