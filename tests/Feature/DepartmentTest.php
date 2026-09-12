<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $usuario = User::factory()->create();
        $this->token = auth('api')->login($usuario);
    }

    ### Criacao com dados validos ###
    public function test_permite_criar_departamento_com_dados_validos(): void
    {
        $payload = [
            'name' => 'Engenharia de Software',
            'description' => 'Time de desenvolvimento e arquitetura.',
            'status' => 'ativo',
        ];

        $resposta = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/departments', $payload);

        $resposta->assertStatus(201)
            ->assertJsonPath('data.name', 'Engenharia de Software')
            ->assertJsonPath('data.status', 'ativo');

        $this->assertDatabaseHas('departments', [
            'name' => 'Engenharia de Software',
            'status' => 'ativo',
        ]);
    }

    ### Validacao com dados invalidos ###
    public function test_valida_campos_obrigatorios_ao_criar_departamento(): void
    {
        $resposta = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/departments', [
                'status' => 'status_invalido',
            ]);

        $resposta->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'status']);
    }

    ### Exibicao de departamento e seus colaboradores ###
    public function test_exibe_departamento_e_seus_colaboradores(): void
    {
        $departamento = Department::factory()->create(['name' => 'Suporte Tecnico']);
        Employee::factory()->count(2)->create(['department_id' => $departamento->id]);

        $resposta = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/departments/{$departamento->id}");

        $resposta->assertStatus(200)
            ->assertJsonPath('data.name', 'Suporte Tecnico')
            ->assertJsonCount(2, 'data.employees');
    }

    ### Atualizacao de departamento existente ###
    public function test_permite_atualizar_departamento(): void
    {
        $departamento = Department::factory()->create(['name' => 'Nome Antigo']);

        $resposta = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/departments/{$departamento->id}", [
                'name' => 'Nome Atualizado',
                'description' => 'Nova descricao.',
            ]);

        $resposta->assertStatus(200)
            ->assertJsonPath('data.name', 'Nome Atualizado');

        $this->assertDatabaseHas('departments', [
            'id' => $departamento->id,
            'name' => 'Nome Atualizado',
        ]);
    }

    ### Bloqueio de exclusao de departamento com colaboradores ativos ###
    public function test_nao_permite_exclusao_de_departamento_com_colaboradores_ativos(): void
    {
        $departamento = Department::factory()->create();
        Employee::factory()->create([
            'department_id' => $departamento->id,
            'status' => 'ativo',
        ]);

        $resposta = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/departments/{$departamento->id}");

        $resposta->assertStatus(422)
            ->assertJson([
                'mensagem' => 'O departamento possui colaboradores ativos e nao pode ser excluido.',
            ]);

        $this->assertDatabaseHas('departments', ['id' => $departamento->id]);
    }

    ### Permissao de exclusao de departamento sem colaboradores ativos ###
    public function test_permite_exclusao_de_departamento_sem_colaboradores_ativos(): void
    {
        $departamento = Department::factory()->create();
        Employee::factory()->create([
            'department_id' => $departamento->id,
            'status' => 'inativo',
        ]);

        $resposta = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/departments/{$departamento->id}");

        $resposta->assertStatus(204);
        $this->assertDatabaseMissing('departments', ['id' => $departamento->id]);
    }

    ### Listagem com paginacao e filtro por status ###
    public function test_lista_departamentos_com_paginacao_e_filtro_por_status(): void
    {
        Department::factory()->count(3)->create(['status' => 'ativo']);
        Department::factory()->count(2)->create(['status' => 'inativo']);

        $resposta = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/departments?status=ativo');

        $resposta->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
}
