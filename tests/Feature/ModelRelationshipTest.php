<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    ### Relacionamento de departamento com colaboradores ###
    public function test_departamento_possui_muitos_colaboradores(): void
    {
        $departamento = Department::factory()->create();
        $colaborador1 = Employee::factory()->create(['department_id' => $departamento->id, 'status' => 'ativo']);
        $colaborador2 = Employee::factory()->create(['department_id' => $departamento->id, 'status' => 'inativo']);

        $this->assertCount(2, $departamento->employees);
        $this->assertCount(1, $departamento->activeEmployees);
        $this->assertEquals($colaborador1->id, $departamento->activeEmployees->first()->id);
    }

    ### Relacionamento de colaborador com departamento ###
    public function test_colaborador_pertence_a_um_departamento(): void
    {
        $departamento = Department::factory()->create(['name' => 'Engenharia de Software']);
        $colaborador = Employee::factory()->create(['department_id' => $departamento->id]);

        $this->assertInstanceOf(Department::class, $colaborador->department);
        $this->assertEquals('Engenharia de Software', $colaborador->department->name);
    }

    ### Funcionamento do soft delete em colaborador ###
    public function test_colaborador_utiliza_soft_delete_preservando_registro(): void
    {
        $colaborador = Employee::factory()->create();
        $colaboradorId = $colaborador->id;

        $colaborador->delete();

        $this->assertSoftDeleted('employees', ['id' => $colaboradorId]);
        $this->assertNotNull(Employee::withTrashed()->find($colaboradorId));
        $this->assertNull(Employee::find($colaboradorId));
    }

    ### Escopo de status ativo e inativo ###
    public function test_escopos_de_status_em_departamento_e_colaborador(): void
    {
        Department::factory()->create(['status' => 'ativo']);
        Department::factory()->create(['status' => 'inativo']);

        $this->assertEquals(1, Department::ativo()->count());
        $this->assertEquals(1, Department::inativo()->count());
    }
}
