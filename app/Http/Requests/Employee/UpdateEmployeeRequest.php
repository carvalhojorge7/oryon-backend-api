<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $colaboradorId = $this->route('employee') ?? $this->route('id');

        return [
            'name' => ['sometimes', 'string', 'max:150'],
            'email' => [
                'sometimes',
                'email',
                'max:150',
                Rule::unique('employees', 'email')->ignore($colaboradorId),
            ],
            'department_id' => ['sometimes', 'integer', 'exists:departments,id'],
            'role' => ['sometimes', 'string', 'max:100'],
            'hired_at' => ['sometimes', 'date'],
            'status' => ['sometimes', 'in:ativo,inativo'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'Informe um endereco de e-mail valido.',
            'email.unique' => 'Ja existe outro colaborador cadastrado com este e-mail.',
            'department_id.exists' => 'O departamento informado nao existe.',
            'hired_at.date' => 'A data de contratacao deve ser uma data valida.',
            'status.in' => 'O status informado e invalido. Utilize ativo ou inativo.',
        ];
    }
}
