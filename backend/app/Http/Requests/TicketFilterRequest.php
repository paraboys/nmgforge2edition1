<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'in:open,pending,resolved,closed'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'assignee' => ['nullable', 'integer'],
            'requester' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'in:created_at,updated_at,priority,status'],
            'direction' => ['nullable', 'in:asc,desc'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
