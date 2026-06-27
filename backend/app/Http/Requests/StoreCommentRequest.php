<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:1'],
            'is_internal' => ['sometimes', 'boolean'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        $user = $this->user();
        if (!($user->isAdmin() || $user->isAgent())) {
            $validated['is_internal'] = false;
        }

        return $validated;
    }
}
