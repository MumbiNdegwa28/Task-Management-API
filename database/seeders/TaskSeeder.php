<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = [
            [
                'title'    => 'Fix critical login bug',
                'due_date' => now()->addDays(1)->toDateString(),
                'priority' => 'high',
                'status'   => 'pending',
            ],
            [
                'title'    => 'Write API documentation',
                'due_date' => now()->addDays(2)->toDateString(),
                'priority' => 'medium',
                'status'   => 'in_progress',
            ],
            [
                'title'    => 'Deploy to production',
                'due_date' => now()->addDays(3)->toDateString(),
                'priority' => 'high',
                'status'   => 'done',
            ],
            [
                'title'    => 'Code review PR #42',
                'due_date' => now()->addDays(4)->toDateString(),
                'priority' => 'medium',
                'status'   => 'pending',
            ],
            [
                'title'    => 'Update dependencies',
                'due_date' => now()->addDays(5)->toDateString(),
                'priority' => 'low',
                'status'   => 'done',
            ],
        ];

        foreach ($tasks as $task) {
            Task::create($task);
        }
    }
}