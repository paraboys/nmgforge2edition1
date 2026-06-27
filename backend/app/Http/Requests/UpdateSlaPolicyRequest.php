<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateSlaPolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'response_time_minutes' => ['required', 'integer', 'min:1'],
            'resolution_time_minutes' => ['required', 'integer', 'min:1', 'gte:response_time_minutes'],
        ];
    }
}
