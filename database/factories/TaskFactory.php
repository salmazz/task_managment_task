<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;


    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(2),
            'status' => 'pending',
            'due_date' => $this->faker->dateTimeBetween('+1 days', '+10 days'),
            'assignee_id' => null,
            'created_by' => null,
        ];
    }

    public function manager(): static
    {
        return $this->state(fn () => ['role' => 'manager']);
    }
}
