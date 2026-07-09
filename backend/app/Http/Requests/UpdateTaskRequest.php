<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $task = $this->route('task');

        if ($user === null || ! $task instanceof Task || ! $user->can('update', $task)) {
            return false;
        }

        if ($user->isAdmin() || ! $this->filled('assigned_to')) {
            return true;
        }

        return (int) $this->input('assigned_to') === $user->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'status' => ['sometimes', 'required', Rule::in(Task::STATUSES)],
            'assigned_to' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'due_date' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
