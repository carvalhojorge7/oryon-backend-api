<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTest extends TestCase
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
    public function test_permite_cadastrar_colaborador_com_dados_validos(): void
    {
        $departamento = Department::factory()->create();

        $payload = [
            'name' => 'Mariana Dias',
            'email' => 'mariana.dias@oryon.com.br',
            'department_id' => $departamento->id,
            'role' => 'Desenvolvedora Backend Pleno',
            'hired_at' => '2024-01-15',
            'status' => 'ativo',
        ];

        $resposta = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/employees', $payload);

        $resposta->assertStatus(201)
            ->assertJsonPath('data.email', 'mariana.dias@oryon.com.br')
            ->assertJsonPath('data.department_id', $departamento->id);

        $this->assertDatabaseHas('employees', [
            'email' => 'mariana.dias@oryon.com.br',
            'department_id' => $departamento->id,
        ]);
    }

    ### Validacao com dados invalidos ###
    public function test_valida_campos_obrigatorios_ao_cadastrar_colaborador(): void
    {
        $resposta = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/employees', []);

        $resposta->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'department_id', 'role', 'hired_at']);
    }

    ### Validacao de e-mail unico ###
    public function test_impede_cadastro_com_email_duplicado(): void
    {
        $departamento = Department::factory()->create();
        Employee::factory()->create([
            'email' => 'duplicado@oryon.com.br',
            'department_id' => $departamento->id,
        ]);

        $resposta = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/employees', [
                'name' => 'Outro Colaborador',
                'email' => 'duplicado@oryon.com.br',
                'department_id' => $departamento->id,
                'role' => 'Analista',
                'hired_at' => '2024-02-01',
            ]);

        $resposta->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    ### Associacao com departamento existente ###
    public function test_impede_cadastro_com_departamento_inexistente(): void
    {
        $resposta = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/employees', [
                'name' => 'Colaborador Teste',
                'email' => 'teste@oryon.com.br',
                'department_id' => 99999,
                'role' => 'Analista',
                'hired_at' => '2024-02-01',
            ]);

        $resposta->assertStatus(422)
            ->assertJsonValidationErrors(['department_id']);
    }

    ### Validacao do campo status ###
    public function test_valida_valores_permitidos_para_o_campo_status(): void
    {
        $departamento = Department::factory()->create();

        $resposta = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/employees', [
                'name' => 'Colaborador Teste',
                'email' => 'teste@oryon.com.br',
                'department_id' => $departamento->id,
                'role' => 'Analista',
                'hired_at' => '2024-02-01',
                'status' => 'status_invalido',
            ]);

        $resposta->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    ### Exibicao de colaborador por ID ###
    public function test_exibe_detalhes_do_colaborador(): void
    {
        $colaborador = Employee::factory()->create(['name' => 'Lucas Ferreira']);

        $resposta = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/employees/{$colaborador->id}");

        $resposta->assertStatus(200)
            ->assertJsonPath('data.name', 'Lucas Ferreira');
    }

    ### Atualizacao de colaborador ###
    public function test_permite_atualizar_colaborador(): void
    {
        $colaborador = Employee::factory()->create(['role' => 'Junior']);

        $resposta = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/employees/{$colaborador->id}", [
                'role' => 'Pleno',
            ]);

        $resposta->assertStatus(200)
            ->assertJsonPath('data.role', 'Pleno');

        $this->assertEquals('Pleno', $colaborador->fresh()->role);
    }

    ### Inativacao do colaborador atraves do endpoint DELETE ###
    public function test_inativa_colaborador_atraves_do_endpoint_delete(): void
    {
        $colaborador = Employee::factory()->create(['status' => 'ativo']);

        $resposta = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/employees/{$colaborador->id}");

        $resposta->assertStatus(200)
            ->assertJson([
                'mensagem' => 'Colaborador inativado com sucesso.',
            ]);

        $this->assertSoftDeleted('employees', ['id' => $colaborador->id]);
        $this->assertEquals('inativo', Employee::withTrashed()->find($colaborador->id)->status);
    }

    ### Listagem com filtros por departamento e status ###
    public function test_lista_colaboradores_com_filtros_e_paginacao(): void
    {
        $depto1 = Department::factory()->create();
        $depto2 = Department::factory()->create();

        Employee::factory()->count(2)->create(['department_id' => $depto1->id, 'status' => 'ativo']);
        Employee::factory()->count(1)->create(['department_id' => $depto1->id, 'status' => 'inativo']);
        Employee::factory()->count(3)->create(['department_id' => $depto2->id, 'status' => 'ativo']);

        $resposta = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/employees?department_id={$depto1->id}&status=ativo");

        $resposta->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
