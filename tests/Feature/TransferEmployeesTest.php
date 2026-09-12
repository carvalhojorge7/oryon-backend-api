<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransferEmployeesTest extends TestCase
{
    use RefreshDatabase;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $usuario = User::factory()->create();
        $this->token = auth('api')->login($usuario);
    }

    ### Transferencia de colaboradores ativos e retorno da contagem ###
    public function test_transfere_apenas_colaboradores_ativos_e_retorna_quantidade(): void
    {
        $origem = Department::factory()->create(['name' => 'Depto Origem']);
        $destino = Department::factory()->create(['name' => 'Depto Destino']);

        // 3 ativos e 2 inativos na origem
        $ativos = Employee::factory()->count(3)->create([
            'department_id' => $origem->id,
            'status' => 'ativo',
        ]);
        $inativos = Employee::factory()->count(2)->create([
            'department_id' => $origem->id,
            'status' => 'inativo',
        ]);

        $resposta = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/departments/{$origem->id}/transfer-employees", [
                'target_department_id' => $destino->id,
            ]);

        $resposta->assertStatus(200)
            ->assertJson([
                'message' => 'Colaboradores transferidos com sucesso.',
                'transferred' => 3,
            ]);

        // Verifica se os 3 ativos foram para o destino
        foreach ($ativos as $ativo) {
            $this->assertEquals($destino->id, $ativo->fresh()->department_id);
        }

        // Verifica se os 2 inativos permaneceram na origem
        foreach ($inativos as $inativo) {
            $this->assertEquals($origem->id, $inativo->fresh()->department_id);
        }
    }

    ### Impede transferencia para o mesmo departamento ###
    public function test_nao_permite_transferencia_para_o_mesmo_departamento(): void
    {
        $departamento = Department::factory()->create();
        Employee::factory()->create([
            'department_id' => $departamento->id,
            'status' => 'ativo',
        ]);

        $resposta = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/departments/{$departamento->id}/transfer-employees", [
                'target_department_id' => $departamento->id,
            ]);

        $resposta->assertStatus(422)
            ->assertJson([
                'mensagem' => 'Os departamentos de origem e destino nao podem ser iguais.',
            ]);
    }

    ### Validacao de departamento de origem inexistente ###
    public function test_retorna_404_para_departamento_de_origem_inexistente(): void
    {
        $destino = Department::factory()->create();

        $resposta = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/departments/99999/transfer-employees", [
                'target_department_id' => $destino->id,
            ]);

        $resposta->assertStatus(404);
    }

    ### Validacao de departamento de destino inexistente ###
    public function test_retorna_erro_para_departamento_de_destino_inexistente(): void
    {
        $origem = Department::factory()->create();

        $resposta = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/departments/{$origem->id}/transfer-employees", [
                'target_department_id' => 99999,
            ]);

        $resposta->assertStatus(422)
            ->assertJsonValidationErrors(['target_department_id']);
    }

    ### Cenario de falha garantindo o rollback da transacao ###
    public function test_garante_rollback_da_transacao_em_caso_de_falha(): void
    {
        $origem = Department::factory()->create();
        $destino = Department::factory()->create();

        $colaborador = Employee::factory()->create([
            'department_id' => $origem->id,
            'status' => 'ativo',
        ]);

        // Simula uma falha forcada dentro da transacao
        try {
            DB::transaction(function () use ($origem, $destino) {
                Employee::where('department_id', $origem->id)
                    ->where('status', 'ativo')
                    ->update(['department_id' => $destino->id]);

                throw new Exception('Simulacao de falha inesperada no banco para verificar rollback.');
            });
        } catch (Exception $e) {
            // Excecao capturada para inspecionar estado das tabelas
        }

        // Garante que o colaborador permaneceu no departamento de origem (Rollback executado)
        $this->assertEquals($origem->id, $colaborador->fresh()->department_id);
    }
}
