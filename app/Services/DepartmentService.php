<?php

namespace App\Services;

use App\Models\Department;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class DepartmentService
{
    ### Invalida o cache de listagem de departamentos ###
    public function limparCache(): void
    {
        if (!Cache::has('departamentos:cache_versao')) {
            Cache::forever('departamentos:cache_versao', 1);
        }
        Cache::increment('departamentos:cache_versao');
    }

    ### Listagem paginada com cache inteligente e filtro por status ###
    public function listar(array $filtros = [], int $porPagina = 15): LengthAwarePaginator
    {
        $versaoCache = Cache::get('departamentos:cache_versao', 1);
        $paginaAtual = (int) ($filtros['page'] ?? request('page', 1));
        $chaveCache = "departamentos:v{$versaoCache}:" . md5(json_encode($filtros) . "_{$paginaAtual}_{$porPagina}");

        return Cache::remember($chaveCache, now()->addMinutes(10), function () use ($filtros, $porPagina) {
            $query = Department::query();

            if (!empty($filtros['status'])) {
                $query->where('status', $filtros['status']);
            }

            return $query->orderBy('name')->paginate($porPagina);
        });
    }

    ### Consulta departamento por ID com colaboradores vinculados ###
    public function buscarPorId(int $id): Department
    {
        return Department::with('employees')->findOrFail($id);
    }

    ### Criacao de novo departamento ###
    public function criar(array $dados): Department
    {
        $departamento = Department::create($dados);
        $this->limparCache();

        return $departamento;
    }

    ### Atualizacao dos dados do departamento ###
    public function atualizar(int $id, array $dados): Department
    {
        $departamento = Department::findOrFail($id);
        $departamento->update($dados);
        $this->limparCache();

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
        $resultado = (bool) $departamento->delete();

        $this->limparCache();

        return $resultado;
    }
}
