<?php

namespace App\Http\Requests\Task;

use App\Exceptions\ManagerOnlyException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Auth\Access\AuthorizationException;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (!auth()->user()?->isManager()) {
            throw new ManagerOnlyException();
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date'    => ['required', 'date', 'after_or_equal:today'],
            'assignee_id' => ['nullable', 'exists:users,id'],
        ];
    }


    protected function failedAuthorization()
    {
        throw new AuthorizationException('Only managers are allowed to create tasks.');
    }
}
