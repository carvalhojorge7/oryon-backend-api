<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'O campo e-mail e obrigatorio.',
            'email.email' => 'Informe um endereco de e-mail valido.',
            'password.required' => 'O campo senha e obrigatorio.',
        ];
    }
}
