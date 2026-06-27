<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'subject' => ['sometimes', 'string', 'min:3', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
        ];

        $user = $this->user();
        if ($user && ($user->isAdmin() || $user->isAgent())) {
            $rules['status'] = ['sometimes', 'in:open,pending,resolved,closed'];
            $rules['priority'] = ['sometimes', 'in:low,medium,high,urgent'];
            $rules['assignee_id'] = ['sometimes', 'nullable', 'integer', 'exists:users,id'];
            $rules['tags'] = ['sometimes', 'nullable', 'array'];
            $rules['tags.*'] = ['string', 'max:50'];
        }

        return $rules;
    }
}
