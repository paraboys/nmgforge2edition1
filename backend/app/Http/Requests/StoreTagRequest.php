<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()->role === 'admin' || Auth::user()->role === 'agent';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
