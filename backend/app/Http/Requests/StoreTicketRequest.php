<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'requester_id' => ['sometimes', 'integer', 'exists:users,id'],
        ];
    }
}
