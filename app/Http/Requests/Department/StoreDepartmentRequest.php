<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:ativo,inativo'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do departamento e obrigatorio.',
            'name.max' => 'O nome do departamento nao pode ultrapassar 150 caracteres.',
            'status.in' => 'O status informado e invalido. Utilize ativo ou inativo.',
        ];
    }
}
