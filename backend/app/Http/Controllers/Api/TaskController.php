<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['sometimes', 'required', Rule::in(Task::STATUSES)],
            'assigned_to' => ['sometimes', 'required', 'integer', 'exists:users,id'],
        ]);

        $user = $request->user();

        $tasks = Task::query()
            ->with('assignee:id,name,email,role')
            ->when(! $user->isAdmin(), fn ($query) => $query->where('assigned_to', $user->id))
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['assigned_to']), fn ($query) => $query->where('assigned_to', $filters['assigned_to']))
            ->latest()
            ->get();

        return response()->json([
            'data' => $tasks,
        ]);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['assigned_to'] ??= $request->user()->id;

        $task = Task::query()->create($data)->load('assignee:id,name,email,role');

        return response()->json([
            'message' => 'Task created successfully.',
            'data' => $task,
        ], 201);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $task->update($request->validated());
        $task->load('assignee:id,name,email,role');

        return response()->json([
            'message' => 'Task updated successfully.',
            'data' => $task,
        ]);
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully.',
        ]);
    }
}
