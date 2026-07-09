<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'status' => ['sometimes', 'required', Rule::in(Task::STATUSES)],
            'assigned_to' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
