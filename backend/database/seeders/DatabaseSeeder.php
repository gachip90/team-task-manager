<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            Task::query()
                ->whereIn('title', [
                    'Prepare API contract',
                    'Build task CRUD endpoints',
                    'Review authorization rules',
                    'Prepare sprint task list',
                    'Implement task filters',
                    'Review overdue tasks',
                    'Update task statuses',
                ])
                ->delete();

            User::query()
                ->whereIn('id', [1, 2, 3])
                ->orWhereIn('email', [
                    'admin@example.com',
                    'user@example.com',
                    'user1@example.com',
                    'user2@example.com',
                ])
                ->delete();

            User::query()->create([
                'id' => 1,
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('Admin@123456'),
                'role' => 'admin',
            ]);

            $user1 = User::query()->create([
                'id' => 2,
                'name' => 'User1',
                'email' => 'user1@example.com',
                'password' => Hash::make('User1@123456'),
                'role' => 'user',
            ]);

            $user2 = User::query()->create([
                'id' => 3,
                'name' => 'User2',
                'email' => 'user2@example.com',
                'password' => Hash::make('User2@123456'),
                'role' => 'user',
            ]);

            Task::query()->create([
                'title' => 'Prepare sprint task list',
                'description' => 'Create the task list for the upcoming sprint planning session.',
                'status' => 'todo',
                'assigned_to' => $user1->id,
                'due_date' => now()->addDays(2)->toDateString(),
            ]);

            Task::query()->create([
                'title' => 'Implement task filters',
                'description' => 'Wire up status filtering and title search for the task screen.',
                'status' => 'in_progress',
                'assigned_to' => $user1->id,
                'due_date' => now()->addDays(5)->toDateString(),
            ]);

            Task::query()->create([
                'title' => 'Review overdue tasks',
                'description' => 'Check stale tasks and update their due dates where needed.',
                'status' => 'todo',
                'assigned_to' => $user2->id,
                'due_date' => now()->addDays(3)->toDateString(),
            ]);

            Task::query()->create([
                'title' => 'Update task statuses',
                'description' => 'Move completed work to done and keep in-progress tasks current.',
                'status' => 'done',
                'assigned_to' => $user2->id,
                'due_date' => null,
            ]);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users AUTO_INCREMENT = 4');
        }
    }
}
