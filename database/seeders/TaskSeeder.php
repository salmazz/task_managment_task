<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $manager = User::where('role', 'manager')->first();
        $users = User::where('role', 'user')->pluck('id');

        $tasks = Task::factory()->count(5)->create([
            'created_by' => $manager->id,
            'assignee_id' => $users->random(),
        ]);

        // Add dependencies to Task 4 and Task 5
        $tasks[3]->dependencies()->attach([$tasks[0]->id, $tasks[1]->id]);
        $tasks[4]->dependencies()->attach([$tasks[2]->id]);
    }
}
