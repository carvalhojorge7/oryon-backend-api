<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class TransferService
{
    ### Executa a transferencia atomica de colaboradores ativos entre departamentos ###
    public function transferir(int $origemId, int $destinoId): int
    {
        ### 1. Verifica se departamento de origem existe ###
        $origem = Department::findOrFail($origemId);

        ### 2. Verifica se departamento de destino existe ###
        $destino = Department::findOrFail($destinoId);

        ### 3. Impede que origem e destino sejam o mesmo departamento ###
        if ($origem->id === $destino->id) {
            throw new DomainException('Os departamentos de origem e destino nao podem ser iguais.');
        }

        ### 4 e 5. Executa a transferencia dentro de transacao com rollback em caso de falha ###
        try {
            return DB::transaction(function () use ($origem, $destino) {
                // Seleciona e atualiza exclusivamente os colaboradores com status ativo
                $quantidadeTransferida = Employee::where('department_id', $origem->id)
                    ->where('status', 'ativo')
                    ->update(['department_id' => $destino->id]);

                return $quantidadeTransferida;
            });
        } catch (Throwable $e) {
            // Em caso de erro nao tratado, repassa a excecao para garantir o rollback
            throw $e;
        }
    }
}
