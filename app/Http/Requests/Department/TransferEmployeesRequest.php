<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;

class TransferEmployeesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_department_id' => ['required', 'integer', 'exists:departments,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'target_department_id.required' => 'O departamento de destino e obrigatorio.',
            'target_department_id.integer' => 'O identificador do departamento de destino deve ser um numero inteiro.',
            'target_department_id.exists' => 'O departamento de destino informado nao foi encontrado.',
        ];
    }
}
