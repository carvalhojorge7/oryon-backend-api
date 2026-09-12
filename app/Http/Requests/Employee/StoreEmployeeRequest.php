<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:employees,email'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'role' => ['required', 'string', 'max:100'],
            'hired_at' => ['required', 'date'],
            'status' => ['sometimes', 'in:ativo,inativo'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do colaborador e obrigatorio.',
            'email.required' => 'O e-mail e obrigatorio.',
            'email.email' => 'Informe um endereco de e-mail valido.',
            'email.unique' => 'Ja existe um colaborador cadastrado com este e-mail.',
            'department_id.required' => 'O departamento vinculado e obrigatorio.',
            'department_id.exists' => 'O departamento informado nao existe.',
            'role.required' => 'O cargo e obrigatorio.',
            'hired_at.required' => 'A data de contratacao e obrigatoria.',
            'hired_at.date' => 'A data de contratacao deve ser uma data valida.',
            'status.in' => 'O status informado e invalido. Utilize ativo ou inativo.',
        ];
    }
}
