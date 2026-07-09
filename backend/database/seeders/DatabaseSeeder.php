<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
        );

        $user = User::query()->updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('password'),
                'role' => 'user',
            ],
        );

        Task::query()->updateOrCreate(
            ['title' => 'Prepare API contract'],
            [
                'description' => 'Draft the initial REST API contract for task management.',
                'status' => 'todo',
                'assigned_to' => $admin->id,
                'due_date' => now()->addDays(2)->toDateString(),
            ],
        );

        Task::query()->updateOrCreate(
            ['title' => 'Build task CRUD endpoints'],
            [
                'description' => 'Implement create, update, list, and delete endpoints.',
                'status' => 'in_progress',
                'assigned_to' => $user->id,
                'due_date' => now()->addDays(5)->toDateString(),
            ],
        );

        Task::query()->updateOrCreate(
            ['title' => 'Review authorization rules'],
            [
                'description' => 'Verify admin and user task permissions.',
                'status' => 'done',
                'assigned_to' => $user->id,
                'due_date' => null,
            ],
        );
    }
}
