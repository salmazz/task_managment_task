<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $task = $this->route('task');

        // Manager can update everything
        if ($user->isManager()) return true;

        // User can only update the status of their assigned task
        return $user->id === $task->assignee_id && $this->has('status');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date'    => ['sometimes', 'date', 'after_or_equal:today'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'status'      => ['sometimes', 'in:pending,completed,canceled'],
        ];
    }
}
