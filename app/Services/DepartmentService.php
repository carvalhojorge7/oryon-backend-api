<?php

namespace App\Services;

use App\Models\Department;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DepartmentService
{
    ### Listagem paginada com filtro por status ###
    public function listar(array $filtros = [], int $porPagina = 15): LengthAwarePaginator
    {
        $query = Department::query();

        if (!empty($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }

        return $query->orderBy('name')->paginate($porPagina);
    }

    ### Consulta departamento por ID com colaboradores vinculados ###
    public function buscarPorId(int $id): Department
    {
        return Department::with('employees')->findOrFail($id);
    }

    ### Criacao de novo departamento ###
    public function criar(array $dados): Department
    {
        return Department::create($dados);
    }

    ### Atualizacao dos dados do departamento ###
    public function atualizar(int $id, array $dados): Department
    {
        $departamento = Department::findOrFail($id);
        $departamento->update($dados);

        return $departamento->fresh();
    }

    ### Exclusao de departamento com validacao de colaboradores ativos ###
    public function excluir(int $id): bool
    {
        $departamento = Department::findOrFail($id);

        ### Regra de Negocio: impede exclusao se houver colaboradores ativos vinculados ###
        if ($departamento->activeEmployees()->exists()) {
            throw new DomainException('O departamento possui colaboradores ativos e nao pode ser excluido.');
        }

        // Remove colaboradores inativos se existirem para manter integridade
        $departamento->employees()->forceDelete();

        return (bool) $departamento->delete();
    }
}
