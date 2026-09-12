<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Services\DepartmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheAndPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected string $token;
    protected User $usuario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->usuario = User::factory()->create();
        $this->token = auth('api')->login($this->usuario);
    }

    ### Validacao de cache e invalidacao na listagem de departamentos ###
    public function test_armazena_listagem_em_cache_e_invalida_em_mutacoes(): void
    {
        Department::factory()->count(2)->create();

        // 1a Chamada: Carrega dados e popula o cache
        $resposta1 = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/departments');
        $resposta1->assertStatus(200);

        $versaoInicial = Cache::get('departamentos:cache_versao', 1);

        // Criacao de novo departamento deve incrementar a versao do cache (invalidação)
        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/departments', [
                'name' => 'Novo Departamento Cache',
                'status' => 'ativo',
            ]);

        $versaoAposCriacao = Cache::get('departamentos:cache_versao');
        $this->assertGreaterThan($versaoInicial, $versaoAposCriacao);
    }

    ### Invalidacao de cache apos transferencia de colaboradores ###
    public function test_invalida_cache_apos_transferencia_de_colaboradores(): void
    {
        $origem = Department::factory()->create();
        $destino = Department::factory()->create();

        Employee::factory()->create(['department_id' => $origem->id, 'status' => 'ativo']);

        $service = app(DepartmentService::class);
        $service->listar(); // Popula o cache

        $versaoAntes = Cache::get('departamentos:cache_versao', 1);

        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/departments/{$origem->id}/transfer-employees", [
                'target_department_id' => $destino->id,
            ]);

        $versaoDepois = Cache::get('departamentos:cache_versao');
        $this->assertGreaterThan($versaoAntes, $versaoDepois);
    }

    ### Validacao de autorizacao via Policies ###
    public function test_policies_autorizam_usuario_autenticado(): void
    {
        $departamento = Department::factory()->create();
        $colaborador = Employee::factory()->create(['department_id' => $departamento->id]);

        $this->assertTrue($this->usuario->can('viewAny', Department::class));
        $this->assertTrue($this->usuario->can('view', $departamento));
        $this->assertTrue($this->usuario->can('create', Department::class));
        $this->assertTrue($this->usuario->can('update', $departamento));
        $this->assertTrue($this->usuario->can('delete', $departamento));

        $this->assertTrue($this->usuario->can('viewAny', Employee::class));
        $this->assertTrue($this->usuario->can('view', $colaborador));
        $this->assertTrue($this->usuario->can('create', Employee::class));
        $this->assertTrue($this->usuario->can('update', $colaborador));
        $this->assertTrue($this->usuario->can('delete', $colaborador));
    }
}
