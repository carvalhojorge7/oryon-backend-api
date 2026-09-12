<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EmployeeService
{
    ### Listagem paginada com filtros por departamento e status ###
    public function listar(array $filtros = [], int $porPagina = 15): LengthAwarePaginator
    {
        $query = Employee::with('department');

        if (!empty($filtros['department_id'])) {
            $query->where('department_id', $filtros['department_id']);
        }

        if (!empty($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }

        return $query->orderBy('name')->paginate($porPagina);
    }

    ### Consulta colaborador por ID ###
    public function buscarPorId(int $id): Employee
    {
        return Employee::with('department')->findOrFail($id);
    }

    ### Criacao de colaborador ###
    public function criar(array $dados): Employee
    {
        return Employee::create($dados);
    }

    ### Atualizacao dos dados do colaborador ###
    public function atualizar(int $id, array $dados): Employee
    {
        $colaborador = Employee::findOrFail($id);
        $colaborador->update($dados);

        return $colaborador->fresh();
    }

    ### Inativacao do colaborador e aplicacao de soft delete ###
    public function inativar(int $id): bool
    {
        $colaborador = Employee::findOrFail($id);

        ### Atualiza status para inativo e aplica soft delete ###
        $colaborador->update(['status' => 'inativo']);

        return (bool) $colaborador->delete();
    }
}
